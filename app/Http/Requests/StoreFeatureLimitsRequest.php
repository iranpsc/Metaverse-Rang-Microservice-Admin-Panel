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
     * @return array<string, mixed>
     */
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['start_date', 'end_date'] as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $normalized[$field] = $this->normalizeJalaliDateString($this->input($field));
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
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
                'regex:/^\d{4}\/\d{2}\/\d{2}$/',
                $this->jalaliDateRule('تاریخ شروع'),
            ],
            'end_date' => [
                'required',
                'string',
                'regex:/^\d{4}\/\d{2}\/\d{2}$/',
                $this->jalaliDateRule('تاریخ پایان'),
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

    /**
     * Accept only plausible Jalali dates (rejects Gregorian years like 2026/09/19).
     */
    private function jalaliDateRule(string $label): Closure
    {
        return function (string $attribute, string $value, Closure $fail) use ($label) {
            try {
                [$year] = array_map('intval', explode('/', $value));

                // Jalali years in use for this app; Gregorian years (~1900+) must be rejected
                if ($year < 1200 || $year > 1600) {
                    $fail("{$label} باید به صورت شمسی وارد شود");

                    return;
                }

                $jalalian = Jalalian::fromFormat('Y/m/d', $value);

                // Reject overflowed/normalized invalid dates (e.g. 1400/99/99)
                if ($jalalian->format('Y/m/d') !== $value) {
                    $fail("فرمت {$label} صحیح نیست");

                    return;
                }

                $carbonDate = $jalalian
                    ->toCarbon()
                    ->timezone(config('app.timezone'))
                    ->startOfDay();

                $overlapMessage = $attribute === 'end_date' ? 'تاریخ پایان تداخل دارد' : 'تاریخ شروع تداخل دارد';

                if (FeatureLimit::where('start_date', '<=', $carbonDate->toDateString())
                    ->where('end_date', '>=', $carbonDate->toDateString())
                    ->exists()) {
                    $fail($overlapMessage);
                }
            } catch (\Throwable $e) {
                $fail("فرمت {$label} صحیح نیست");
            }
        };
    }

    private function normalizeJalaliDateString(string $value): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $normalized = str_replace($persian, $english, trim($value));
        $normalized = str_replace('-', '/', $normalized);

        if (preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $normalized, $matches)) {
            return sprintf('%04d/%02d/%02d', (int) $matches[1], (int) $matches[2], (int) $matches[3]);
        }

        return $normalized;
    }
}
