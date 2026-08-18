<?php

namespace App\Services\Lands;

use App\Models\FeatureLimit;
use App\Models\FeatureProperties;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class FeatureLimitService
{
    private const SYSTEM_OWNER_ID = 1;

    /**
     * @return array{feature_limits: array<int, mixed>, pagination: array<string, mixed|null>}
     */
    public function getPaginated(int $perPage, int $page): array
    {
        $featureLimits = FeatureLimit::orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $featureLimits->getCollection()->transform(function ($limit) {
            $startDate = is_string($limit->start_date)
                ? Carbon::parse($limit->start_date)
                : ($limit->start_date instanceof Carbon ? $limit->start_date : null);
            $endDate = is_string($limit->end_date)
                ? Carbon::parse($limit->end_date)
                : ($limit->end_date instanceof Carbon ? $limit->end_date : null);

            $limit->expired = $endDate ? now()->isAfter($endDate) : false;
            $limit->start_date_shamsi = $startDate ? Jalalian::fromCarbon($startDate)->format('Y/m/d') : null;
            $limit->end_date_shamsi = $endDate ? Jalalian::fromCarbon($endDate)->format('Y/m/d') : null;

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
            $startDateCarbon = Jalalian::fromFormat('Y/m/d', $validated['start_date'])->toCarbon();
            $endDateCarbon = Jalalian::fromFormat('Y/m/d', $validated['end_date'])->toCarbon();

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
                'start_date' => $startDateCarbon->toDateString(),
                'end_date' => $endDateCarbon->toDateString(),
                'price_limit' => $validated['price_limit'],
                'price' => $validated['price_limit'] ? $validated['price'] : 0,
                'individual_buy_limit' => $validated['individual_buy_limit'],
                'individual_buy_count' => $validated['individual_buy_limit'] ? $validated['individual_buy_count'] : 0,
            ]);

            $this->applyLimits($featureLimit);

            return $featureLimit;
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $featureLimit = FeatureLimit::findOrFail($id);

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
