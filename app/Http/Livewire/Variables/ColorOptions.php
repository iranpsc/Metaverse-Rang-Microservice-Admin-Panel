<?php

namespace App\Http\Livewire\Variables;

use App\Models\Option;
use App\Models\Variable;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;
use App\Models\Admin;

class ColorOptions extends Component
{
    public $options, $asset, $amount, $phoneVerification, $access_password;
    private $variables, $admin;

    public function mount()
    {
        $this->options = Option::all();
        $this->variables = Variable::all();
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
            ]);

            $this->resetErrorBag();
            $this->resetValidation();
            Session::forget('verify_code');
            session()->flash('success', 'پکیج رنگ وارد شد');
        }
        $this->options = Option::all();
    }

    public function updated($propertyName) {
        $this->validateOnly($propertyName);
    }

    public function delete($id) {
        Option::destroy($id);
        session()->flash('success', 'بسته حذف شد');
        $this->options = Option::all();
    }

    public function hydrate() {
        $this->variables = Variable::all();
        $this->admin = Admin::first();
    }

    public function render()
    {
        return view('livewire.variables.color-options', [
            'variables' => $this->variables
        ]);
    }
}
