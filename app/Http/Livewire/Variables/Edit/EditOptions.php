<?php

namespace App\Http\Livewire\Variables\Edit;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;
use App\Models\Admin;

class EditOptions extends Component
{
    public $option, $asset, $amount, $phoneVerification, $access_password, $note;

    public function mount($option)
    {
        $this->option = $option;
        $this->asset = $option->asset;
        $this->amount = $option->amount;
    }

    protected $rules = [
        'phoneVerification' => 'required|numeric',
        'access_password' => 'required',
        'amount' => 'required|numeric|min:1',
        'asset' => 'required|in:red,blue,yellow,psc,irr'
    ];

    protected $messages = [
        'phoneVerification.required' => 'کد تایید را وارد کنید',
        'access_password.required' => 'رمز دسترسی را وارد کنید',
        'amount.required' => 'قیمت را وارد کنید',
        'amount.numberic' => 'مقدار عددی برای قیمت وارد کنید',
        'amount.min' => 'کمترین مقدار قیمت 1 است',
        'asset.required' => 'رنگ را انتخاب کنید',
        'asset.in' => 'گزینه انتخاب شده معتبر نمی باشد'
    ];

    public function sendSMS()
    {
        $verify_code = random_int(100000, 999999);

        $admin =Admin::first();

        Session::put('verify_code', $verify_code);

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

        $admin =Admin::first();

        if ($this->phoneVerification != Session::get('verify_code')) {
            $this->addError('phoneVerification', 'کد تایید وارد شده صحیح نمی باشد');
        } else if (!password_verify($this->access_password, $admin->access_password)) {
            $this->addError('access_password', 'رمز دسترسی صحیح نمی باشد');
        } else {
            $this->option->priceChangeLogs()->create([
                'changer_name' => auth()->user()->name,
                'previous_price' => $this->option->amount,
                'current_price' => $this->amount,
                'note' => $this->note,
            ]);

            $this->option->update([
                'asset' => $this->asset,
                'amount' => $this->amount,
                'note' => $this->note,
            ]);

            $this->resetErrorBag();
            $this->resetValidation();
            Session::forget('verify_code');
            session()->flash('success', 'بسته بروزرسانی شد');
        }
    }

    public function updated($propertyName) {
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        return view('livewire.variables.edit.edit-options');
    }
}
