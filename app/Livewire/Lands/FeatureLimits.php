<?php

namespace App\Livewire\Lands;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FeatureLimit;
use App\Traits\SendsVerificationSms;
use Livewire\Attributes\Rule;

class FeatureLimits extends Component
{
    use WithPagination, SendsVerificationSms;

    #[Rule('required|boolean')]
    public $verified_kyc_limit = false;

    #[Rule('required|boolean')]
    public $verified_bank_account_limit = false;

    #[Rule('required|boolean')]
    public $not_sellable = false;

    #[Rule('required|boolean')]
    public $under_18_limit = false;

    #[Rule('required|boolean')]
    public $more_than_18_limit = false;

    #[Rule('required|boolean')]
    public $dynasty_owner_limit = false;

    #[Rule('required|string|max:255')]
    public $title;

    #[Rule('required|string|exists:feature_properties,id')]
    public $start_id;

    #[Rule('required|string|exists:feature_properties,id')]
    public $end_id;

    #[Rule('required|date')]
    public $start_date;

    #[Rule('required|date|after:start_date')]
    public $end_date;

    #[Rule('required|numeric|min:0')]
    public $price = 0;

    #[Rule('required|numeric|min:0')]
    public $individual_buy_limit = 0;

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
            'price' => $this->price,
            'individual_buy_limit' => $this->individual_buy_limit,
        ]);

        $this->resetExcept('admin');

        $this->dispatch('notify', message: 'محدودیت املاک با موفقیت ایجاد شد');
    }

    public function delete(FeatureLimit $feature_limit)
    {
        $feature_limit->delete();

        $this->dispatch('notify', message: 'محدودیت املاک با موفقیت حذف شد');
    }

    #[Title('محدودیت املاک')]
    public function render()
    {
        return view('livewire.lands.feature-limits', [
            'feature_limits' => FeatureLimit::paginate(10),
        ]);
    }
}
