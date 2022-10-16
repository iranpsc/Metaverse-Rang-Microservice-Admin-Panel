<?php

namespace App\Http\Livewire\Variables\Edit;

use Livewire\Component;
use App\Models\Admin;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;
use App\Models\VariableChangeLog;

class EditColors extends Component
{
    public $price, $note, $asset;

    public $phoneVerification;
    public $access_password;

    public function mount($asset)
    {
        $this->asset = $asset;
        $this->price = $asset->price;
    }

    protected $rules = [
        'phoneVerification' => 'required|numeric',
        'access_password' => 'required',
        'price' => 'required|numeric|min:1',
    ];

    protected $messages = [
        'phoneVerification.required' => 'کد تایید را وارد کنید',
        'access_password.required' => 'رمز دسترسی را وارد کنید',
        'price.required' => 'قیمت را وارد کنید',
        'price.numeric' => 'مقدار عددی برای قیمت وارد کنید',
        'price.min' => 'کمترین مقدار قیمت 1 است',
    ];

    public function sendSMS()
    {
        $verify_code = random_int(100000, 999999);

        Session::put('verify_code', $verify_code);

        $admin = Admin::first();

        $result = SMS::send($admin->phone, $verify_code);
        if(is_array($result)) {
            foreach($result as $r) {
                session()->flash('success', 'کد تایید با موفقیت ارسال شد');
            }
        } else {
            session()->flash('error', explode(":", $result)[1]);
        }
    }

    public function update() {

        $this->validate();
        $this->admin = Admin::first();

        if ($this->phoneVerification != Session::get('verify_code')) {
            $this->addError('phoneVerification', 'کد تایید وارد شده صحیح نمی باشد');
        } else if (!password_verify($this->access_password, $this->admin->access_password)) {
            $this->addError('access_password', 'رمز دسترسی صحیح نمی باشد');
        } else {

            $this->asset->priceChangeLogs()->create([
                'changer_name' => auth()->user()->name,
                'previous_price' => $this->asset->price,
                'current_price' => $this->price,
                'note' => $this->note,
            ]);

            $this->asset->update([
                'price' => $this->price,
                'note' => $this->note
            ]);

            $this->resetErrorBag();
            $this->resetValidation();
            Session::forget('verify_code');
            session()->flash('success', 'ارز بروزرسانی شد');
        }
    }

    public function updated($propertyName) {
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        return view('livewire.variables.edit.edit-colors');
    }
}
