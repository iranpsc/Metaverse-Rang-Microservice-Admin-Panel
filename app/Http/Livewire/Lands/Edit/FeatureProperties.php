<?php

namespace App\Http\Livewire\Lands\Edit;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;
use Illuminate\Support\Facades\Auth;

class FeatureProperties extends Component
{
    public $feature, $phoneVerification, $access_password;
    public $properties_id, $area, $density, $karbari, $address, $admin;

    public function mount($feature) {
        $this->admin = Auth::guard('admin')->user();
        $this->properties_id = $feature->properties->id;
        $this->area = $feature->properties->area;
        $this->area = $feature->properties->area;
        $this->density = $feature->properties->density;
        $this->karbari = $feature->properties->karbari;
        $this->address = $feature->properties->address;
    }

    protected $rules = [
        'phoneVerification' => 'required|numeric',
        'access_password' => 'required',
        'area' => 'required|numeric',
        'density' => 'required|numeric',
        'karbari' => 'required|string',
        'address' => 'required|string',
    ];

    protected $messages = [
        'phoneVerification.required' => 'کد تایید را وارد کنید',
        'phoneVerification.numeric' => 'کد تایید صحیح نیست',
        'access_password.required' => 'رمز دسترسی را وارد کنید',
        'area.required' => 'مساحت را وارد کنید',
        'area.numeric' => 'مقدار عددی برای مساحت وارد کنید',
        'density.required' => 'تراکم را وارد کنید',
        'density.numeric' => 'مقدار عددی برای تراکم وارد کنید',
        'karbari.required' => 'کاربری را وارد کنید',
        'karbari.string' => 'مقدار حروفی برای کاربری وارد کنید',
        'address.required' => 'آدرس را وارد کنید'
    ];

    public function sendSMS()
    {
        if(session()->has('verify_code')) {
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
            Session::forget('verify_code');
            $this->feature->properties->update([
                'area'    => $this->area,
                'density' => $this->density,
                'karbari' => $this->karbari,
                'address' => $this->address,
            ]);
            session()->flash('success', 'مشخصات ملک با موفقیت بروزرسانی شد');
            $this->emitUp('featureUpdated');
        }
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.lands.edit.feature-properties');
    }
}
