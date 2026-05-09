<?php

namespace App\Livewire\Lands;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FeatureLimit;
use App\Traits\SendsVerificationSms;
use Illuminate\Validation\Rule;
use App\Models\FeatureProperties;

class FeatureLimits extends Component
{
    use WithPagination, SendsVerificationSms;

    public $verified_kyc_limit = false;
    public $verified_bank_account_limit = false;
    public $not_sellable = false;
    public $under_18_limit = false;
    public $more_than_18_limit = false;
    public $dynasty_owner_limit = false;
    public $title;
    public $start_id;
    public $end_id;
    public $start_date;
    public $end_date;
    public $price_limit = false;
    public $price = 0;
    public $individual_buy_limit = false;
    public $individual_buy_count = 0;

    protected function rules()
    {
        return [
            'verified_kyc_limit' => 'required|boolean',
            'verified_bank_account_limit' => 'required|boolean',
            'not_sellable' => 'required|boolean',
            'under_18_limit' => 'required|boolean',
            'more_than_18_limit' => 'required|boolean',
            'dynasty_owner_limit' => 'required|boolean',
            'title' => 'required|string|max:255',
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (FeatureLimit::where('start_date', '<=', $value)->where('end_date', '>=', $value)->exists()) {
                        $fail('تاریخ شروع تداخل دارد');
                    }
                }
            ],
            'end_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (FeatureLimit::where('start_date', '<=', $value)->where('end_date', '>=', $value)->exists()) {
                        $fail('تاریخ پایان تداخل دارد');
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
                Rule::requiredIf(fn() => app()->environment() === 'production'),
            ],
            'access_password' => [
                'nullable',
                Rule::requiredIf(fn() => app()->environment() === 'production'),
            ]
        ];
    }

    public function mount()
    {
        $this->admin = auth()->user();
        $this->start_date = now()->toDateString();
        $this->end_date = now()->addDays(30)->toDateString();
    }

    public function save()
    {
        $this->validate();

        FeatureLimit::create([
            'verified_kyc_limit' => $this->verified_kyc_limit,
            'verified_bank_account_limit' => $this->verified_bank_account_limit,
            'not_sellable' => $this->not_sellable,
            'under_18_limit' => $this->under_18_limit,
            'more_than_18_limit' => $this->more_than_18_limit,
            'dynasty_owner_limit' => $this->dynasty_owner_limit,
            'title' => $this->title,
            'start_id' => $this->start_id,
            'end_id' => $this->end_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'price_limit' => $this->price_limit,
            'price' => $this->price_limit ? $this->price : 0,
            'individual_buy_limit' => $this->individual_buy_limit,
            'individual_buy_count' => $this->individual_buy_limit ? $this->individual_buy_count : 0,
        ]);

        $this->limitFeatures();

        $this->resetExcept('admin');

        $this->dispatch('notify', message: 'محدودیت املاک با موفقیت ایجاد شد');
    }

    private function limitFeatures()
    {
        $startId = explode('-', trim($this->start_id));
        $endId = explode('-', trim($this->end_id));

        FeatureProperties::where('id_prefix', $startId[0])
            ->whereBetween('id_postfix', [$startId[1], $endId[1]])
            ->whereHas('feature', function ($query) {
                $query->where('owner_id', 1);
            })->chunk(100, function ($features) {
                foreach ($features as $feature) {
                    if ($this->not_sellable) {
                        $feature->update([
                            'rgb' => $this->getSellLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($this->dynasty_owner_limit) {
                        $feature->update([
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($this->verified_kyc_limit) {
                        $feature->update([
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($this->verified_bank_account_limit) {
                        $feature->update([
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($this->under_18_limit) {
                        $feature->update([
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($this->more_than_18_limit) {
                        $feature->update([
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($this->price_limit) {
                        $feature->update([
                            'stability' => $this->price,
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }

                    if ($this->individual_buy_limit) {
                        $feature->update([
                            'rgb' => $this->getLimitedFeatureRGB($feature),
                        ]);
                    }
                }
            });
    }

    private function getLimitedFeatureRGB(FeatureProperties $feature)
    {
        return match ($feature->karbari) {
            'm' => 'g',
            't' => 'n',
            'a' => 'uu',
            default => 'rgb',
        };
    }

    private function getSellLimitedFeatureRGB(FeatureProperties $feature)
    {
        return match ($feature->karbari) {
            'm' => 'f',
            't' => 'm',
            'a' => 'tt',
            default => 'rgb',
        };
    }

    public function delete(FeatureLimit $feature_limit)
    {
        $this->removeLimits($feature_limit);

        $feature_limit->delete();

        $this->dispatch('notify', message: 'محدودیت املاک با موفقیت حذف شد');
    }

    private function removeLimits(FeatureLimit $feature_limit)
    {
        $startId = explode('-', trim($feature_limit->start_id));
        $endId = explode('-', trim($feature_limit->end_id));

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

    private function getFeatureRGB(FeatureProperties $feature)
    {
        return match ($feature->karbari) {
            'm' => 'd',
            't' => 'k',
            'a' => 'r',
            default => 'rgb',
        };
    }

    #[Title('محدودیت املاک')]
    public function render()
    {
        return view('livewire.lands.feature-limits', [
            'feature_limits' => FeatureLimit::paginate(10),
        ]);
    }
}
