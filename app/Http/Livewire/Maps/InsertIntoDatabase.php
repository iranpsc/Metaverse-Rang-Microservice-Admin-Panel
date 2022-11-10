<?php

namespace App\Http\Livewire\Maps;

use App\Helpers\SMS;
use App\Jobs\ImportMaps;
use App\Models\Map;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class InsertIntoDatabase extends Component
{
    public $map, $code = '', $accessPassword = '', $admin;

    protected $rules = [
        'code' => 'required|integer|min:5',
        'accessPassword' => 'required|integer|min:6'
    ];

    protected $messages = [
        'code.required' => 'کد تایید را وارد کنید',
        'code.integer' => 'کد تایید صحیح نیست',
        'code.min' => 'کد تایید صحیح نیست',
        'accessPassword.required' => 'رمز را وارد کنید',
        'accessPassword.integer' => 'رمز صحیح نمی باشد',
        'accessPassword.min' => 'رمز صحیح نمی باشد',
    ];

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function sendCode()
    {;
        if (Cache::get('maps-verify-code-' . $this->admin->id)) {
            session()->flash('error', 'کد تایید قبلا برای شما ارسال شده است');
            return;
        }
        $verifyCode = random_int(10000, 99999);
        Cache::put('maps-verify-code-' . $this->admin->id, Hash::make($verifyCode), now()->addMinutes(5));
        $result = SMS::send($this->admin->phone, $verifyCode);

        if(is_array($result)) {
            foreach($result as $r) {
                session()->flash('success', $r->statustext);
            }
        } else {
            session()->flash('error', explode(":", $result)[1]);
        }
    }

    public function insertIntoDatabase(Map $map)
    {
        $this->validate();

        if($map->status)
        {
            session()->flash('error', 'اطلاعات قبلا وارد دیتابیس شده است');
            return;
        }

        $cachedCode = Cache::get('maps-verify-code-' . $this->admin->id);

        if (!$cachedCode || Hash::check($cachedCode, $this->code)) {
            $this->addError('code', 'کد تایید وارد شده صحیح نیست');
        } else if (!Hash::check($this->accessPassword, $this->admin->access_password)) {
            $this->addError('accessPassword', 'رمز دسترسی صحیح نیست');
        } else {
            ImportMaps::dispatch($map);
            $map->update(['status' => 1]);
            session()->flash('success', 'اطلاعات با موفقیت وارد دیتابیس شد');
            Cache::delete('maps-verify-code-' . $this->admin->id);
            $this->emitUp('mapsInsertedToDatabase');
        }
    }

    public function render()
    {
        return view('livewire.maps.insert-into-database');
    }
}
