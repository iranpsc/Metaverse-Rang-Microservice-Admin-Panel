<?php

namespace App\Http\Livewire\Variables;

use Livewire\Component;
use App\Models\Variable;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;
use App\Models\Admin;

class ColorsPrice extends Component
{
    public $price;
    public $asset, $variables;

    public $phoneVerification;
    public $access_password;

    public function mount()
    {
        $this->variables = Variable::all();
    }

    protected $rules = [
        'phoneVerification' => 'required|numeric',
        'access_password' => 'required',
        'price' => 'required|numeric|min:1',
        'asset' => 'required|in:red,blue,yellow,irr,psc,satisfaction|unique:variables'
    ];

    protected $messages = [
        'asset.required' => 'نام ارز را وارد کنید',
        'asset.in' => 'نام ارز معتبر نمی باشد',
        'asset.unique' => 'این ارز قبلا تعریف شده است',
        'phoneVerification.required' => 'کد تایید را وارد کنید',
        'access_password.required' => 'رمز دسترسی را وارد کنید',
        'price.required' => 'قیمت را وارد کنید',
        'price.numberic' => 'مقدار عددی برای قیمت وارد کنید',
        'price.min' => 'کمترین مقدار قیمت 1 است',
    ];

    public function sendSMS()
    {
        $verify_code = random_int(100000, 999999);

        Session::put('verify_code', $verify_code);

        $admin = Admin::first();

        $result = SMS::send($admin->phone, $verify_code);
        if (is_array($result)) {
            foreach ($result as $r) {
                session()->flash('success', 'کد تایید با موفقیت ارسال شد');
            }
        } else {
            session()->flash('error', explode(":", $result)[1]);
        }
    }

    public function save()
    {

        $this->validate();
        $this->admin = Admin::first();

        if ($this->phoneVerification != Session::get('verify_code')) {
            $this->addError('phoneVerification', 'کد تایید وارد شده صحیح نمی باشد');
        } else if (!password_verify($this->access_password, $this->admin->access_password)) {
            $this->addError('access_password', 'رمز دسترسی صحیح نمی باشد');
        } else {
            Variable::create([
                'asset' => $this->asset,
                'price' => $this->price,
            ]);

            $this->resetErrorBag();
            $this->resetValidation();
            Session::forget('verify_code');
            session()->flash('success', 'قیمت رنگ با موفقیت وارد شد');
            $this->variables = Variable::all();
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function delete($id) {
        Variable::destroy($id);
        $this->variables = Variable::all();
    }

    public function render()
    {
        return view('livewire.variables.colors-price');
    }
}
