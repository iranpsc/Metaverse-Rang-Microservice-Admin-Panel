<?php

namespace App\Livewire\Lands;

use Livewire\Component;
use App\Models\Feature\FeaturePricingLimit;
use Livewire\Attributes\Title;

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

    public function save()
    {
        $this->validate();
        if (!$this->price_limits) {
            $this->price_limits = new FeaturePricingLimit();
        }
        $this->price_limits = FeaturePricingLimit::updateOrCreate(
            ['id' => $this->price_limits->id],
            [
                'under_eighteen_price_limit' => $this->under_eighteen_price_limit,
                'public_price_limit' => $this->public_price_limit
            ]
        );

        $this->dispatch('notify', message: 'محدودیت‌های قیمت با موفقیت به‌روزرسانی شدند');
    }

    #[Title('محدودیت‌های قیمت')]
    public function render()
    {
        return view('livewire.lands.feature-pricing-limits');
    }
}
