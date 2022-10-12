<?php

namespace App\Http\Livewire\Lands\Edit;

use Livewire\Component;
use App\Models\Admin;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;

class EditFeatureModal extends Component
{
    public $feature, $phoneVerification, $access_password;
    public $properties_id, $area, $density, $karbari, $address;

    public function mount($feature) {
        $this->properties_id = $feature->properties->id;
        $this->area = $feature->properties->area;
        $this->area = $feature->properties->area;
        $this->density = $feature->properties->density;
        $this->karbari = $feature->properties->karbari;
        $this->address = $feature->properties->address;

        $this->admin = Admin::first();

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
        'access_password.required' => 'رمز دسترسی را وارد کنید',
        'area.required' => 'قیمت را وارد کنید',
        'area.numberic' => 'مقدار عددی برای مساحت وارد کنید',
        'density.required' => 'تراکم را وارد کنید',
        'density.numberic' => 'مقدار عددی برای تراکم وارد کنید',
        'karbari.required' => 'کاربری را وارد کنید',
        'karbari.string' => 'مقدار حروفی برای کاربری وارد کنید',
        'address.required' => 'آدرس را وارد کنید'
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
            $this->feature->features_property->update([
                'area' => $this->area,
                'density' => $this->density,
                'karbari' => $this->karbari,
                'address' => $this->address,
            ]);

            $this->resetErrorBag();
            $this->resetValidation();
            Session::forget('verify_code');
            session()->flash('success', 'مشخصات ملک با موفقیت بروزرسانی شد');
        }
    }

    public function render()
    {
        return view('livewire.lands.edit.edit-feature-modal');
    }
}
