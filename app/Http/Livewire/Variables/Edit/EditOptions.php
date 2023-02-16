<?php

namespace App\Http\Livewire\Variables\Edit;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;

class EditOptions extends Component
{
    use WithFileUploads;

    public $option, $code, $asset, $amount, $image, $phoneVerification, $access_password, $note, $admin;

    public function mount($option)
    {
        $this->option = $option;
        $this->asset = $option->asset;
        $this->amount = $option->amount;
        $this->code = $option->code;
        $this->admin = Auth::guard('admin')->user();
    }

    protected $rules = [
        'phoneVerification' => 'required|numeric',
        'access_password' => 'required',
        'amount' => 'required|numeric|min:1',
        'code' => 'required|string',
        'note' => 'required',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,bmp',
        'code' => 'required|string|unique:options,code'
    ];

    protected $messages = [
        'phoneVerification.required' => 'کد تایید را وارد کنید',
        'access_password.required' => 'رمز دسترسی را وارد کنید',
        'amount.required' => 'قیمت را وارد کنید',
        'amount.numeric' => 'مقدار عددی برای قیمت وارد کنید',
        'amount.min' => 'کمترین مقدار قیمت 1 است',
        'asset.required' => 'رنگ را انتخاب کنید',
        'note.required' => 'دلیل بروزرسانی را وارد کنید',
        'image.image' => 'فرمت فایل صحیح نیست',
        'image.mimes' => 'فرمت فایل صحیح نیست',
        'code.required' => 'کد بسته را وارد کنید',
        'code.unique' => 'کد بسته باید تکراری نباشد.'
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

    public function update()
    {

        $this->validate();

        if (!Hash::check($this->phoneVerification, Session::get('verify_code'))) {
            $this->addError('phoneVerification', 'کد تایید وارد شده صحیح نمی باشد');
        } else if (!password_verify($this->access_password, $this->admin->access_password)) {
            $this->addError('access_password', 'رمز دسترسی صحیح نمی باشد');
        } else {
            $this->option->priceChangeLogs()->create([
                'changer_name' => $this->admin->name,
                'previous_value' => $this->option->amount,
                'current_value' => $this->amount,
                'note' => $this->note,
            ]);

            $this->option->update([
                'asset' => $this->asset,
                'amount' => $this->amount,
                'note' => $this->note,
                'code' => $this->code,
            ]);

            if ($this->image) {
                $url = env('FTP_ENDPOINT') . $this->image->store('public/packages');

                if ($this->option->image) {
                    $this->option->image->update([
                        'url' => $url,
                    ]);
                } else {
                    $this->option->image()->create([
                        'url' => $url,
                    ]);
                }
            }

            Session::forget('verify_code');
            $this->emitUp('packageUpdated');
            $this->emit('packageUpdated');
            session()->flash('success', 'بسته بروزرسانی شد');
        }
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.variables.edit.edit-options');
    }
}
