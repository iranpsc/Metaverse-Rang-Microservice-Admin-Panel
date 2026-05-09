<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeatureLimit;
use App\Models\FeatureProperties;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Morilog\Jalali\Jalalian;

class FeatureLimitsController extends Controller
{
    /**
     * Get paginated feature limits
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $featureLimits = FeatureLimit::orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Add expired status and convert dates to Shamsi for display
        $featureLimits->getCollection()->transform(function ($limit) {
            // Parse dates - they might be strings from database or Carbon instances
            $startDate = is_string($limit->start_date) 
                ? \Carbon\Carbon::parse($limit->start_date) 
                : ($limit->start_date instanceof \Carbon\Carbon ? $limit->start_date : null);
            $endDate = is_string($limit->end_date) 
                ? \Carbon\Carbon::parse($limit->end_date) 
                : ($limit->end_date instanceof \Carbon\Carbon ? $limit->end_date : null);
            
            $limit->expired = $endDate ? now()->isAfter($endDate) : false;
            // Convert Carbon dates to Shamsi for display
            $limit->start_date_shamsi = $startDate ? Jalalian::fromCarbon($startDate)->format('Y/m/d') : null;
            $limit->end_date_shamsi = $endDate ? Jalalian::fromCarbon($endDate)->format('Y/m/d') : null;
            return $limit;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'feature_limits' => $featureLimits->items(),
                'pagination' => [
                    'current_page' => $featureLimits->currentPage(),
                    'last_page' => $featureLimits->lastPage(),
                    'per_page' => $featureLimits->perPage(),
                    'total' => $featureLimits->total(),
                    'from' => $featureLimits->firstItem(),
                    'to' => $featureLimits->lastItem(),
                ],
            ],
            'message' => 'Feature limits retrieved successfully.',
        ]);
    }

    /**
     * Store a new feature limit
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'verified_kyc_limit' => 'required|boolean',
            'verified_bank_account_limit' => 'required|boolean',
            'not_sellable' => 'required|boolean',
            'under_18_limit' => 'required|boolean',
            'more_than_18_limit' => 'required|boolean',
            'dynasty_owner_limit' => 'required|boolean',
            'title' => 'required|string|max:255',
            'start_date' => [
                'required',
                'string',
                'regex:/^\d{4}\/\d{2}\/\d{2}$/',
                function ($attribute, $value, $fail) {
                    try {
                        // Convert Shamsi to Carbon for comparison
                        $carbonDate = Jalalian::fromFormat('Y/m/d', $value)->toCarbon();
                        if (FeatureLimit::where('start_date', '<=', $carbonDate->toDateString())
                            ->where('end_date', '>=', $carbonDate->toDateString())->exists()) {
                            $fail('تاریخ شروع تداخل دارد');
                        }
                    } catch (\Exception $e) {
                        $fail('فرمت تاریخ شروع صحیح نیست');
                    }
                }
            ],
            'end_date' => [
                'required',
                'string',
                'regex:/^\d{4}\/\d{2}\/\d{2}$/',
                function ($attribute, $value, $fail) {
                    try {
                        // Convert Shamsi to Carbon for comparison
                        $carbonDate = Jalalian::fromFormat('Y/m/d', $value)->toCarbon();
                        if (FeatureLimit::where('start_date', '<=', $carbonDate->toDateString())
                            ->where('end_date', '>=', $carbonDate->toDateString())->exists()) {
                            $fail('تاریخ پایان تداخل دارد');
                        }
                    } catch (\Exception $e) {
                        $fail('فرمت تاریخ پایان صحیح نیست');
                    }
                }
            ],
            'start_id' => 'required|string|exists:feature_properties,id',
            'end_id' => 'required|string|exists:feature_properties,id',
            'price_limit' => 'required|boolean',
            'price' => 'required|numeric|min:0',
            'individual_buy_limit' => 'required|boolean',
            'individual_buy_count' => 'required|numeric|min:0',
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'integer',
                'digits:6',
                'is_valid_verify_code',
            ],
        ]);

        // Validate that start_id and end_id have the same prefix
        $startIdParts = explode('-', trim($validated['start_id']));
        $endIdParts = explode('-', trim($validated['end_id']));

        if ($startIdParts[0] !== $endIdParts[0]) {
            return response()->json([
                'success' => false,
                'message' => 'پیشوند شناسه های شروع و پایان باید یکسان باشند',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Convert Shamsi dates to Gregorian (Carbon) before saving
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

            $this->limitFeatures($featureLimit);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'محدودیت املاک با موفقیت ایجاد شد',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد محدودیت: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a feature limit
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        // Validate verification code in production
        $validated = $request->validate([
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'integer',
                'digits:6',
                'is_valid_verify_code',
            ],
        ]);

        $featureLimit = FeatureLimit::findOrFail($id);

        DB::beginTransaction();
        try {
            $this->removeLimits($featureLimit);
            $featureLimit->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'محدودیت املاک با موفقیت حذف شد',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف محدودیت',
            ], 500);
        }
    }

    /**
     * Apply limits to features
     *
     * @param FeatureLimit $featureLimit
     * @return void
     */
    private function limitFeatures(FeatureLimit $featureLimit): void
    {
        $startId = explode('-', trim($featureLimit->start_id));
        $endId = explode('-', trim($featureLimit->end_id));

        FeatureProperties::where('id_prefix', $startId[0])
            ->whereBetween('id_postfix', [$startId[1], $endId[1]])
            ->whereHas('feature', function ($query) {
                $query->where('owner_id', 1);
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

    /**
     * Remove limits from features
     *
     * @param FeatureLimit $featureLimit
     * @return void
     */
    private function removeLimits(FeatureLimit $featureLimit): void
    {
        $startId = explode('-', trim($featureLimit->start_id));
        $endId = explode('-', trim($featureLimit->end_id));

        FeatureProperties::where('id_prefix', $startId[0])
            ->whereBetween('id_postfix', [$startId[1], $endId[1]])
            ->whereHas('feature', function ($query) {
                $query->where('owner_id', 1);
            })->chunk(100, function ($features) {
                foreach ($features as $feature) {
                    $feature->update([
                        'stability' => $feature->density * $feature->area,
                        'rgb' => $this->getFeatureRGB($feature),
                    ]);
                }
            });
    }

    /**
     * Get limited feature RGB based on karbari
     *
     * @param FeatureProperties $feature
     * @return string
     */
    private function getLimitedFeatureRGB(FeatureProperties $feature): string
    {
        return match ($feature->karbari) {
            'm' => 'g',
            't' => 'n',
            'a' => 'uu',
            default => 'rgb',
        };
    }

    /**
     * Get sell limited feature RGB based on karbari
     *
     * @param FeatureProperties $feature
     * @return string
     */
    private function getSellLimitedFeatureRGB(FeatureProperties $feature): string
    {
        return match ($feature->karbari) {
            'm' => 'f',
            't' => 'm',
            'a' => 'tt',
            default => 'rgb',
        };
    }

    /**
     * Get default feature RGB based on karbari
     *
     * @param FeatureProperties $feature
     * @return string
     */
    private function getFeatureRGB(FeatureProperties $feature): string
    {
        return match ($feature->karbari) {
            'm' => 'd',
            't' => 'k',
            'a' => 'r',
            default => 'rgb',
        };
    }
}

