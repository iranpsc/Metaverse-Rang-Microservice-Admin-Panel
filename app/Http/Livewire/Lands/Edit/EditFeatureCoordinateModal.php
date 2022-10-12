<?php

namespace App\Http\Livewire\Lands\Edit;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;
use App\Models\Admin;

class EditFeatureCoordinateModal extends Component
{
    public $feature, $coordinates, $num, $phoneVerification, $access_password, $admin;
    public $x = [];
    public $y = [];

    public function mount($feature) {
        $this->feature = $feature;
        $this->coordinates = $feature->geometry->coordinates;

        foreach($this->coordinates as $key => $coordinate) {
            $this->x[$key] = $coordinate->x;
            $this->y[$key] = $coordinate->y;
        }

        $this->admin = Admin::first();
    }

    protected $rules = [
        'phoneVerification' => 'required',
        'access_password' => 'required',
    ];

    public function sendSMS()
    {
        $verify_code = random_int(100000, 999999);

        Session::put('verify_code', $verify_code);

        $result = SMS::send($this->admin->phone, $verify_code);
        if(is_array($result)) {
            foreach($result as $r) {
                session()->flash('success', 'کد تایید با موفقیت ارسال شد');
            }
        } else {
            session()->flash('error', explode(":", $result)[1]);
        }
    }

    public function save() {

        $this->validate();

        if ($this->phoneVerification != Session::get('verify_code')) {
            $this->addError('phoneVerification', 'کد تایید وارد شده صحیح نمی باشد');
        } else if (!password_verify($this->access_password, $this->admin->access_password)) {
            $this->addError('access_password', 'رمز دسترسی صحیح نمی باشد');
        } else {
            foreach($this->coordinates as $key => $coordinate) {
                $coordinate->update([
                    'x' => $this->x[$key],
                    'y' => $this->y[$key],
                ]);
            }

            $this->resetErrorBag();
            $this->resetValidation();
            Session::forget('verify_code');
            session()->flash('success', 'مختصات ملک با موفقیت بروزرسانی شد');
        }
    }

    public function render()
    {
        return view('livewire.lands.edit.edit-feature-coordinate-modal');
    }
}
