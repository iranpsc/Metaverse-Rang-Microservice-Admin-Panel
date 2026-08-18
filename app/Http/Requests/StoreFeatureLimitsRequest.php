<?php

namespace App\Http\Requests;

use App\Models\FeatureLimit;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Morilog\Jalali\Jalalian;

class StoreFeatureLimitsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('admin')?->hasRole('super-admin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'verified_kyc_limit' => ['required', 'boolean'],
            'verified_bank_account_limit' => ['required', 'boolean'],
            'not_sellable' => ['required', 'boolean'],
            'under_18_limit' => ['required', 'boolean'],
            'more_than_18_limit' => ['required', 'boolean'],
            'dynasty_owner_limit' => ['required', 'boolean'],
            'title' => ['required', 'string', 'max:255'],
            'start_date' => [
                'required',
                'string',
                'date:Y/m/d',
                function (string $attribute, string $value, Closure $fail) {
                    try {
                        $carbonDate = Jalalian::fromFormat('Y/m/d', $value)->toCarbon();
                        if (FeatureLimit::where('start_date', '<=', $carbonDate->toDateString())
                            ->where('end_date', '>=', $carbonDate->toDateString())->exists()) {
                            $fail('تاریخ شروع تداخل دارد');
                        }
                    } catch (\Exception $e) {
                        $fail('فرمت تاریخ شروع صحیح نیست');
                    }
                },
            ],
            'end_date' => [
                'required',
                'string',
                'date:Y/m/d',
                function (string $attribute, string $value, Closure $fail) {
                    try {
                        $carbonDate = Jalalian::fromFormat('Y/m/d', $value)->toCarbon();
                        if (FeatureLimit::where('start_date', '<=', $carbonDate->toDateString())
                            ->where('end_date', '>=', $carbonDate->toDateString())->exists()) {
                            $fail('تاریخ پایان تداخل دارد');
                        }
                    } catch (\Exception $e) {
                        $fail('فرمت تاریخ پایان صحیح نیست');
                    }
                },
            ],
            'start_id' => ['required', 'string', 'exists:feature_properties,id'],
            'end_id' => ['required', 'string', 'exists:feature_properties,id'],
            'price_limit' => ['required', 'boolean'],
            'price' => ['required', 'numeric', 'min:0'],
            'individual_buy_limit' => ['required', 'boolean'],
            'individual_buy_count' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $startId = $this->input('start_id');
            $endId = $this->input('end_id');

            if (! is_string($startId) || ! is_string($endId)) {
                return;
            }

            $startIdParts = explode('-', trim($startId));
            $endIdParts = explode('-', trim($endId));

            if (($startIdParts[0] ?? null) !== ($endIdParts[0] ?? null)) {
                $validator->errors()->add('end_id', 'پیشوند شناسه های شروع و پایان باید یکسان باشند');
            }
        });
    }
}
