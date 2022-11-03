<?php

namespace App\Http\Livewire\Variables\Edit;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;
use Illuminate\Support\Facades\Auth;

class EditColors extends Component
{
    public $price, $note, $asset;

    public $phoneVerification;
    public $access_password, $admin;

    public function mount($asset)
    {
        $this->asset = $asset;
        $this->price = $asset->price;
        $this->admin = Auth::guard('admin')->user();
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

        $result = SMS::send($this->admin->phone, $verify_code);
        if(is_array($result)) {
            foreach($result as $r) {
                session()->flash('success', $r->statustext);
            }
        } else {
            session()->flash('error', explode(":", $result)[1]);
        }
    }

    public function update() {

        $this->validate();
        if ($this->phoneVerification != Session::get('verify_code')) {
            $this->addError('phoneVerification', 'کد تایید وارد شده صحیح نمی باشد');
        } else if (!password_verify($this->access_password, $this->admin->access_password)) {
            $this->addError('access_password', 'رمز دسترسی صحیح نمی باشد');
        } else {

            $this->asset->priceChangeLogs()->create([
                'changer_name' => $this->admin->name,
                'previous_price' => $this->asset->price,
                'current_price' => $this->price,
                'note' => $this->note,
            ]);

            $this->asset->update([
                'price' => $this->price,
                'note' => $this->note
            ]);

            Session::forget('verify_code');
            session()->flash('success', 'ارز بروزرسانی شد');
            $this->emitUp('currencyUpdated');
            $this->emit('currencyUpdated');
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
