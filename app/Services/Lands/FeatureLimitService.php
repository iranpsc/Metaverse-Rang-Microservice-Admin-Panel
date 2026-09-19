<?php

namespace App\Services\Lands;

use App\Models\FeatureLimit;
use App\Models\FeatureProperties;
use App\Policies\FeatureLimitPolicy;
use Carbon\Carbon;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class FeatureLimitService
{
    private const SYSTEM_OWNER_ID = 1;

    /**
     * @return array{feature_limits: array<int, mixed>, pagination: array<string, mixed|null>}
     */
    public function getPaginated(int $perPage, int $page, ?string $search = null): array
    {
        $query = FeatureLimit::query()->orderBy('created_at', 'desc');

        if ($search !== null && trim($search) !== '') {
            $query->where('title', 'like', '%'.trim($search).'%');
        }

        $featureLimits = $query->paginate($perPage, ['*'], 'page', $page);

        $featureLimits->getCollection()->transform(function ($limit) {
            $limit->expired = $limit->isExpired();
            // Convert from raw Y-m-d (not UTC ISO) to avoid timezone day-shift
            $limit->start_date_shamsi = $this->toShamsiDate($limit->getRawOriginal('start_date') ?? $limit->start_date);
            $limit->end_date_shamsi = $this->toShamsiDate($limit->getRawOriginal('end_date') ?? $limit->end_date);

            return $limit;
        });

        return [
            'feature_limits' => $featureLimits->items(),
            'pagination' => $this->formatPagination($featureLimits),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated): FeatureLimit
    {
        return DB::transaction(function () use ($validated) {
            $featureLimit = FeatureLimit::create([
                'verified_kyc_limit' => $validated['verified_kyc_limit'],
                'verified_bank_account_limit' => $validated['verified_bank_account_limit'],
                'not_sellable' => $validated['not_sellable'],
                'under_18_limit' => $validated['under_18_limit'],
                'more_than_18_limit' => $validated['more_than_18_limit'],
                'dynasty_owner_limit' => $validated['dynasty_owner_limit'],
                'title' => $validated['title'],
                'start_id' => $validated['start_id'],
                'end_id' => $validated['end_id'],
                'start_date' => $this->jalaliToGregorianDateString($validated['start_date']),
                'end_date' => $this->jalaliToGregorianDateString($validated['end_date']),
                'price_limit' => $validated['price_limit'],
                'price' => $validated['price_limit'] ? $validated['price'] : 0,
                'individual_buy_limit' => $validated['individual_buy_limit'],
                'individual_buy_count' => $validated['individual_buy_limit'] ? $validated['individual_buy_count'] : 0,
            ]);

            $this->applyLimits($featureLimit);

            return $featureLimit;
        });
    }

    /**
     * Convert a Jalali Y/m/d string to a Gregorian Y-m-d date string in app timezone.
     */
    private function jalaliToGregorianDateString(string $jalaliDate): string
    {
        return Jalalian::fromFormat('Y/m/d', $jalaliDate)
            ->toCarbon()
            ->timezone(config('app.timezone'))
            ->startOfDay()
            ->toDateString();
    }

    /**
     * Convert a stored Gregorian date to Jalali Y/m/d using the date portion only.
     */
    private function toShamsiDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            $carbon = Carbon::instance(\DateTime::createFromInterface($value))
                ->timezone(config('app.timezone'))
                ->startOfDay();

            return Jalalian::fromCarbon($carbon)->format('Y/m/d');
        }

        if (is_string($value)) {
            // Use Y-m-d only — datetime/ISO strings can shift the calendar day in UTC
            $dateOnly = substr($value, 0, 10);
            $carbon = Carbon::createFromFormat('Y-m-d', $dateOnly, config('app.timezone'));

            if ($carbon === false) {
                return null;
            }

            return Jalalian::fromCarbon($carbon->startOfDay())->format('Y/m/d');
        }

        return null;
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $featureLimit = FeatureLimit::findOrFail($id);

            // Invoke policy directly so the expired rule applies to all roles
            // (Gate::before would otherwise allow super-admins through).
            $policy = app(FeatureLimitPolicy::class);
            if (! $policy->delete(auth()->user(), $featureLimit)) {
                throw new DomainException('محدودیت منقضی‌شده قابل حذف نیست.');
            }

            $this->removeLimits($featureLimit);
            $featureLimit->delete();
        });
    }

    private function applyLimits(FeatureLimit $featureLimit): void
    {
        $startId = explode('-', trim($featureLimit->start_id));
        $endId = explode('-', trim($featureLimit->end_id));

        FeatureProperties::where('id_prefix', $startId[0])
            ->whereBetween('id_postfix', [$startId[1], $endId[1]])
            ->whereHas('feature', function ($query) {
                $query->where('owner_id', self::SYSTEM_OWNER_ID);
            })->chunk(100, function ($features) use ($featureLimit) {
                foreach ($features as $feature) {
                    if ($featureLimit->not_sellable) {
                        $feature->update([
                            'rgb' => $this->getSellLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($featureLimit->dynasty_owner_limit) {
                        $feature->update([
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($featureLimit->verified_kyc_limit) {
                        $feature->update([
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($featureLimit->verified_bank_account_limit) {
                        $feature->update([
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($featureLimit->under_18_limit) {
                        $feature->update([
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($featureLimit->more_than_18_limit) {
                        $feature->update([
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($featureLimit->price_limit) {
                        $feature->update([
                            'stability' => $featureLimit->price,
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($featureLimit->individual_buy_limit) {
                        $feature->update([
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }
                }
            });
    }

    private function removeLimits(FeatureLimit $featureLimit): void
    {
        $startId = explode('-', trim($featureLimit->start_id));
        $endId = explode('-', trim($featureLimit->end_id));

        FeatureProperties::where('id_prefix', $startId[0])
            ->whereBetween('id_postfix', [$startId[1], $endId[1]])
            ->whereHas('feature', function ($query) {
                $query->where('owner_id', self::SYSTEM_OWNER_ID);
            })->chunk(100, function ($features) {
                foreach ($features as $feature) {
                    $feature->update([
                        'stability' => $feature->density * $feature->area,
                        'rgb' => $this->getFeatureRGB($feature),
                    ]);
                }
            });
    }

    private function getLimitedFeatureRGB(FeatureProperties $feature): string
    {
        return match ($feature->karbari) {
            'm' => 'g',
            't' => 'n',
            'a' => 'uu',
            default => 'rgb',
        };
    }

    private function getSellLimitedFeatureRGB(FeatureProperties $feature): string
    {
        return match ($feature->karbari) {
            'm' => 'f',
            't' => 'm',
            'a' => 'tt',
            default => 'rgb',
        };
    }

    private function getFeatureRGB(FeatureProperties $feature): string
    {
        return match ($feature->karbari) {
            'm' => 'd',
            't' => 'k',
            'a' => 'r',
            default => 'rgb',
        };
    }

    /**
     * @return array<string, mixed|null>
     */
    private function formatPagination(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
