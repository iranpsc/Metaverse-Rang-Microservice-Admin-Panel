<?php

namespace App\Http\Livewire\Lands;

use Livewire\Component;
use App\Models\Feature\FeaturePricingLimit;

class FeaturePricingLimits extends Component
{
    public $price_limits, $public_price_limit, $under_eighteen_price_limit;

    public function mount()
    {
        $this->price_limits = FeaturePricingLimit::first();
        $this->public_price_limit = $this->price_limits->public_price_limit ?? 0;
        $this->under_eighteen_price_limit = $this->price_limits->under_eighteen_price_limit ?? 0;
    }

    protected $rules = [
        'public_price_limit' => 'required|integer',
        'under_eighteen_price_limit' => 'required|integer'
    ];
    protected $messages = [
        'public_price_limit.required' => 'مقدار محدودیت قیمت گذاری برای عموم را مشخص کنید',
        'public_price_limit.integer' => 'مقدار محدودیت قیمت گذاری برای عموم صحیح نیست',
        'under_eighteen_price_limit.required' => 'مقدار محدودیت قیمت گذاری برای زیر ۱۸ سال را مشخص کنید',
        'under_eighteen_price_limit.integer' => 'مقدار محدودیت قیمت گذاری برای زیر ۱۸ سال صحیح نیست'
    ];

    public function save() {
        $this->validate();
        if(! $this->price_limits) {
            $this->price_limits = new FeaturePricingLimit();
        }
        $this->price_limits = FeaturePricingLimit::updateOrCreate(
            ['id' => $this->price_limits->id],
            [
            'under_eighteen_price_limit' => $this->under_eighteen_price_limit,
            'public_price_limit' => $this->public_price_limit
            ]
        );

        session()->flash('success', 'محدودیت قیمت اعمال شد');
    }

    public function render()
    {
        return view('livewire.lands.feature-pricing-limits');
    }
}
