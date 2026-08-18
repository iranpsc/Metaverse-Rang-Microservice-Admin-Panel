<?php

namespace App\Livewire\Lands\Edit;

use App\Models\Feature;
use Livewire\Component;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;

class FeatureCoordinates extends Component
{
    use SendsVerificationSms;

    public Feature $feature;

    public $db_coordinates, $coordinates = [];

    public function mount()
    {
        $this->db_coordinates = $this->feature->geometry->coordinates;

        foreach ($this->db_coordinates as $key => $coordinate) {
            $this->coordinates[$key] = [
                'x' => $coordinate->x,
                'y' => $coordinate->y
            ];
        }

        $this->admin = Auth::guard('admin')->user();
    }

    protected $rules = [
        'coordinates.*.x' => 'required|numeric',
        'coordinates.*.y' => 'required|numeric',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function save()
    {
        $this->validate();

        foreach ($this->db_coordinates as $key => $coordinate) {
            $coordinate->update([
                'x' => $this->coordinates[$key]['x'],
                'y' => $this->coordinates[$key]['y'],
            ]);
        }

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }
}
