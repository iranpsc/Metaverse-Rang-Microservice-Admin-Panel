<?php

namespace App\Http\Livewire\SystemVariables;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use App\Helpers\SMS;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Update extends Component
{
    public $slug, $name, $value, $variable, $code, $accessPassword, $admin, $note;

    protected $rules = [
        'slug' => 'required|string',
        'name' => 'required|string|min:2',
        'value' => 'required|numeric|min:0',
        'code' => 'required|integer',
        'accessPassword' => 'required'
    ];

    protected $messages = [
        'slug.required' => 'اسلاگ را وارد کنید',
        'slug.string' => 'اسلاگ صحیح نیست',
        'name.required' => 'نام متغیر را وارد کنید',
        'name.string' => 'نام متغیر صحیح نیست',
        'name.min' => 'نام متغیر صحیح نیست',
        'value.required' => 'مقدار متغییر را وارد کنید',
        'value.numeric' => 'مقدار متغییر صحیح نیست',
        'value.min' => 'کمترین مقدار 0 می باشد',
        'code.required' => 'کد تایید را وارد کنید',
        'code.integer' => 'کد تایید صحیح نیست',
        'accessPassword.required' => 'رمز دسترسی را وارد کنید'
    ];

    public function mount()
    {
        $this->name = $this->variable->name;
        $this->slug = $this->variable->slug;
        $this->value = $this->variable->value;
        $this->admin = Auth::guard('admin')->user();
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function sendCode()
    {
        if (Cache::get('system-variables-verify-code-' . $this->admin->id)) {
            session()->flash('error', 'کد تایید قبلا برای شما ارسال شده است');
            return;
        }
        $verifyCode = random_int(10000, 99999);
        Cache::put('system-variables-verify-code-' . $this->admin->id, Hash::make($verifyCode), now()->addMinutes(5));
        $result = SMS::send($this->admin->phone, $verifyCode);

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

        $cachedCode = Cache::get('system-variables-verify-code-' . $this->admin->id);


        if (!$cachedCode || Hash::check($cachedCode, $this->code)) {
            $this->addError('code', 'کد تایید وارد شده صحیح نیست');
        } else if (!Hash::check($this->accessPassword, $this->admin->access_password)) {
            $this->addError('accessPassword', 'رمز دسترسی صحیح نیست');
        } else {
            $this->variable->changeLogs()->create([
                'changer_name' => $this->admin->name,
                'previous_value' => $this->variable->value,
                'current_value' => $this->value,
                'note' => $this->note,
            ]);
            $this->variable->update([
                'slug' => $this->slug,
                'name' => $this->name,
                'value' => $this->value,
            ]);
            session()->flash('success', 'متغییر ویرایش شد.');
            Cache::delete('system-variables-verify-code-' . $this->admin->id);
            $this->emitUp('variableUpdated');
        }
    }

    public function render()
    {
        return view('livewire.system-variables.update');
    }
}
