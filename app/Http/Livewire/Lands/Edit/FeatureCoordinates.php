<?php

namespace App\Http\Livewire\Lands\Edit;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;
use Illuminate\Support\Facades\Auth;

class FeatureCoordinates extends Component
{
    public $feature, $db_coordinates, $coordinates = [], $phoneVerification, $access_password, $admin;

    public function mount($feature) {
        $this->feature = $feature;
        $this->db_coordinates = $feature->geometry->coordinates;

        foreach($this->db_coordinates as $key => $coordinate) {
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
        'phoneVerification' => 'required|numeric',
        'access_password' => 'required',
    ];

    protected $messages = [
        'coordinates.*.x.required' => 'مقدار X را وارد کنید',
        'coordinates.*.x.numeric' => 'مقدار عددی وارد کنید',
        'coordinates.*.y.required' => 'مقدار Y را وارد کنید',
        'coordinates.*.y.numeric' => 'مقدار عددی وارد کنید',
        'phoneVerification.required' => 'کد تایید را وارد کنید',
        'phoneVerification.numeric' => 'کد تایید صحیح نیست',
        'access_password.required' => 'رمز دسترسی را وارد کنید',
    ];

    public function sendSMS()
    {

        if(Session::has('verify_code'))
        {
            Session::forget('verify_code');
        }

        $verify_code = random_int(100000, 999999);

        Session::put('verify_code', $verify_code);

        $result = SMS::send($this->admin->phone, $verify_code);
        if(is_array($result)) {
            foreach($result as $r) {
                session()->flash('success', $r->statustext);
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
            foreach($this->db_coordinates as $key => $coordinate) {
                $coordinate->update([
                    'x' => $this->coordinates[$key]['x'],
                    'y' => $this->coordinates[$key]['y'],
                ]);
            }
            Session::forget('verify_code');
            session()->flash('success', 'مختصات ملک با موفقیت بروزرسانی شد');
            $this->emitUp('featureUpdated');
        }
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.lands.edit.feature-coordinates');
    }
}
