<?php

namespace App\Http\Livewire\Variables;

use App\Models\Option;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;
use App\Models\Admin;

class ColorOptions extends Component
{
    public $options, $variables, $asset, $amount, $phoneVerification, $access_password;
    public $admin;

    protected $rules = [
        'phoneVerification' => 'required|numeric',
        'access_password' => 'required',
        'amount' => 'required|integer|min:1',
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

    protected $listeners = [
        'packageCreated' => '$refresh',
        'packageDeleted' => '$refresh'
    ];

    public function mount()
    {
        $this->admin = Admin::where('role', 'super-admin')->first();
    }

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
            Option::create([
                'asset' => $this->asset,
                'amount' => $this->amount,
                'code' => random_int(100000, 999999)
            ]);

            $this->resetErrorBag();
            $this->resetValidation();
            Session::forget('verify_code');
            session()->flash('success', 'پکیج رنگ وارد شد');
            $this->emitSelf('packageCreated');
        }
    }

    public function updated($propertyName) {
        $this->validateOnly($propertyName);
    }

    public function delete($id) {
        $this->emitSelf('packageDeleted');
        $this->emitTo('packages-change-logs', 'delete-change-logs', $id);
        Option::destroy($id);
        session()->flash('success', 'بسته حذف شد');
    }

    public function render()
    {
        return view('livewire.variables.color-options');
    }
}
