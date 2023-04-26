<?php

namespace App\Http\Livewire\Lands\Edit;

use Livewire\Component;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;

class FeatureCoordinates extends Component
{
    use SendsVerificationSms;

    public $feature, $db_coordinates, $coordinates = [];

    public function mount($feature)
    {
        $this->feature = $feature;
        $this->db_coordinates = $feature->geometry->coordinates;

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
        $this->clearVerificationCode();
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'اطلاعات با موفقیت ثبت شد']);
        $this->emitUp('featureUpdated');
    }

    public function render()
    {
        return view('livewire.lands.edit.feature-coordinates');
    }
}
