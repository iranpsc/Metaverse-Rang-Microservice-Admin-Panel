<?php

namespace App\Http\Livewire\Lands\Edit;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;

class FeatureProperties extends Component
{
    use SendsVerificationSms;

    public $properties_id, $area, $density, $karbari, $address, $rgb, $feature;

    public function mount($feature)
    {
        $this->admin = Auth::guard('admin')->user();
        $this->properties_id = $feature->properties->id;
        $this->area = $feature->properties->area;
        $this->area = $feature->properties->area;
        $this->density = $feature->properties->density;
        $this->karbari = $feature->properties->karbari;
        $this->address = $feature->properties->address;
        $this->rgb = $feature->properties->rgb;
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
        $this->clearVerificationCode();
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'اطلاعات با موفقیت ثبت شد']);
        $this->emitUp('featureUpdated');
    }

    public function render()
    {
        return view('livewire.lands.edit.feature-properties');
    }
}
