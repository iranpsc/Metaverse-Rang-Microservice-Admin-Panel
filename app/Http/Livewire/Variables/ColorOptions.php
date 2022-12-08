<?php

namespace App\Http\Livewire\Variables;

use App\Models\Option;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Helpers\SMS;
use App\Models\Admin;
use App\Models\Variable;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;

class ColorOptions extends Component
{
    use WithPagination, WithFileUploads;

    public $asset, $amount, $image, $phoneVerification, $access_password;
    public $admin;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'phoneVerification' => 'required|numeric',
        'access_password' => 'required',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,bmp',
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
        'asset.in' => 'گزینه انتخاب شده معتبر نمی باشد',
        'image.image' => 'فرمت فایل صحیح نیست',
        'image.mimes' => 'فرمت فایل صحیح نیست',
    ];

    protected $listeners = [
        'deletePackage' => 'delete',
        'packageCreated' => '$refresh',
        'packageUpdated' => '$refresh',
        'packageDeleted' => '$refresh'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function sendSMS()
    {
        $verify_code = random_int(100000, 999999);
        Session::put('verify_code', Hash::make($verify_code));
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
        if (! Hash::check($this->phoneVerification, Session::get('verify_code'))) {
            $this->addError('phoneVerification', 'کد تایید وارد شده صحیح نمی باشد');
        } else if (!password_verify($this->access_password, $this->admin->access_password)) {
            $this->addError('access_password', 'رمز دسترسی صحیح نمی باشد');
        } else {
            $option = Option::create([
                'asset' => $this->asset,
                'amount' => $this->amount,
                'code' => random_int(100000, 999999)
            ]);

            if($this->image)
            {
                $url = env('FTP_ENDPOINT') . $this->image->store('public/packages');

                $option->image()->create([
                    'url' => $url,
                ]);
            }

            $this->resetExcept('admin');
            Session::forget('verify_code');
            session()->flash('success', 'پکیج رنگ وارد شد');
            $this->emitSelf('packageCreated');
        }
    }

    public function updated($propertyName) {
        $this->validateOnly($propertyName);
    }

    public function delete(Option $option) {
        $this->emitSelf('packageDeleted');
        $this->emit('packageDeleted');
        $option->image()->delete();
        $option->priceChangeLogs()->delete();
        $option->delete();
    }

    public function render()
    {
        return view('livewire.variables.color-options', [
            'variables' => Variable::all('asset'),
            'options'   => Option::with('priceChangeLogs')->paginate(10)
        ]);
    }
}
