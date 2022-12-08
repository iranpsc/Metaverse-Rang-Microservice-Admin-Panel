<?php

namespace App\Http\Livewire\SystemVariables;

use App\Models\SystemVariable;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use App\Helpers\SMS;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Listing extends Component
{
    public $slug, $name, $value, $variables, $code, $accessPassword, $admin;

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

    protected $listeners = [
        'variableCreated' => '$refresh',
        'variableUpdated' => '$refresh',
        'variableDeleted' => '$refresh',
        'deleteSystemVariable' => 'delete',
    ];

    public function mount()
    {
        $this->variables = SystemVariable::with('changeLogs')->get();
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

    public function save()
    {
        $this->validate();

        $cachedCode = Cache::get('system-variables-verify-code-' . $this->admin->id);


        if (!$cachedCode || Hash::check($cachedCode, $this->code)) {
            $this->addError('code', 'کد تایید وارد شده صحیح نیست');
        } else if (!Hash::check($this->accessPassword, $this->admin->access_password)) {
            $this->addError('accessPassword', 'رمز دسترسی صحیح نیست');
        } else {
            SystemVariable::create([
                'slug' => $this->slug,
                'name' => $this->name,
                'value' => $this->value,
            ]);
            session()->flash('success', 'متغییر ایجاد شد!');
            Cache::delete('system-variables-verify-code-' . $this->admin->id);
            $this->emitSelf('variableCreated');
        }
    }

    public function delete(SystemVariable $systemVariable) {
        $systemVariable->changeLogs()->delete();
        $systemVariable->delete();
        session()->flash('success', 'متغییر حذف شد!');
        $this->emitSelf('variableDeleted');
    }

    public function render()
    {
        return view('livewire.system-variables.listing')
            ->extends('layouts.app')
            ->section('content');
    }
}
