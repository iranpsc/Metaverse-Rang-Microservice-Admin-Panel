<?php

namespace App\Livewire\Lands\Edit;

use App\Models\Feature;
use Livewire\Component;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;

class FeatureProperties extends Component
{
    use SendsVerificationSms;

    public Feature $feature;

    public $properties_id, $area, $density, $karbari, $address, $rgb;

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
        $this->properties_id = $this->feature->properties->id;
        $this->area = $this->feature->properties->area;
        $this->density = $this->feature->properties->density;
        $this->karbari = $this->feature->properties->karbari;
        $this->address = $this->feature->properties->address;
        $this->rgb = $this->feature->properties->rgb;
    }

    protected $rules = [
        'area' => 'required|numeric',
        'density' => 'required|numeric',
        'karbari' => 'required|string',
        'address' => 'required|string',
        'rgb' => 'required|string',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function save()
    {

        $this->validate();

        $this->feature->properties->update([
            'area'    => $this->area,
            'density' => $this->density,
            'karbari' => $this->karbari,
            'address' => $this->address,
            'rgb' => $this->rgb,
        ]);

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }
}
