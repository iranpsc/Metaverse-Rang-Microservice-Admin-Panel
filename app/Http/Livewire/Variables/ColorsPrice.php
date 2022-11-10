<?php

namespace App\Http\Livewire\Variables;

use Livewire\Component;
use App\Models\Variable;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ColorsPrice extends Component
{
    public $price;
    public $asset;

    public $admin, $phoneVerification;
    public $access_password;

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    protected $listeners = [
        'deleteCurrency' => 'delete',
        'currencyCreated' => '$refresh',
        'currencyUpdated' => '$refresh',
        'currencyDeleted' => '$refresh',
    ];

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

        Session::put('verify_code', Hash::make($verify_code));

        $result = SMS::send($this->admin->phone, $verify_code);
        if (is_array($result)) {
            foreach ($result as $r) {
                session()->flash('success', $r->statustext);
            }
        } else {
            session()->flash('error', explode(":", $result)[1]);
        }
    }

    public function save()
    {

        $this->validate();

        if (! Hash::check($this->phoneVerification, Session::get('verify_code'))) {
            $this->addError('phoneVerification', 'کد تایید وارد شده صحیح نمی باشد');
        } else if (!password_verify($this->access_password, $this->admin->access_password)) {
            $this->addError('access_password', 'رمز دسترسی صحیح نمی باشد');
        } else {
            Variable::create([
                'asset' => $this->asset,
                'price' => $this->price,
            ]);

            Session::forget('verify_code');
            session()->flash('success', 'قیمت رنگ با موفقیت وارد شد');
            $this->resetExcept('admin');
            $this->emitSelf('currencyCreated');
        }
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function delete(Variable $variable) {
        $variable->priceChangeLogs()->delete();
        $variable->delete();
        $this->emitSelf('currencyDeleted');
        $this->emit('currencyDeleted');
    }

    public function render()
    {
        return view('livewire.variables.colors-price', [
            'variables' => Variable::lazy()
        ]);
    }
}
