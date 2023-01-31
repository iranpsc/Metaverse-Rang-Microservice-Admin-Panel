<?php

namespace App\Http\Livewire\IpManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Helpers\SMS;
use App\Models\Ip;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class AdminAllowedIps extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $allowedIp = [], $title, $code, $accessPassword, $admin;

    protected $listeners = [
        'newIpAdded' => '$refresh',
        'deleteAdminIp' => 'deleteIp',
        'ipDeleted' => '$refresh',
    ];

    protected $rules = [
        'title' => 'required|string',
        'code' => 'required|integer|min:6',
        'accessPassword' => 'required',
        'allowedIp.*' => 'required|integer|min:0|max:255',
    ];

    protected $messages = [
        'title.required' => 'عنوان را وارد کنید',
        'code.required' => 'کد تایید را وارد کنید',
        'code.integer' => 'کد تایید صحیح نمی باشد',
        'code.min' => 'کد تایید صحیح نمی باشد',
        'accessPassword.required' => 'رمز دسترسی را وارد کنید',
        'allowedIp.*.required' => 'مقداری تعیین کنید',
        'allowedIp.*.integer' => 'مقدار صحیح نمی باشد',
        'allowedIp.*.min' => 'مقدار صحیح نمی باشد',
        'allowedIp.*.max' => 'مقدار صحیح نمی باشد',
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function sendCode()
    {
        $verifyCode = random_int(100000, 999999);
        Cache::put('ips-verify-code-' . $this->admin->id, Hash::make($verifyCode), now()->addMinutes(5));
        $this->reset('code');
        $result = SMS::send($this->admin->phone, $verifyCode);

        if (is_array($result)) {
            foreach ($result as $r) {
                session()->flash('success', $r->statustext);
            }
        } else {
            session()->flash('error', explode(":", $result)[1]);
            Cache::delete('ips-verify-code-' . $this->admin->id);
        }
    }

    public function save()
    {
        $this->validate();

        $cachedCode = Cache::get('ips-verify-code-' . $this->admin->id);

        if (!$cachedCode || !Hash::check($this->code, $cachedCode)) {
            $this->addError('code', 'کد تایید وارد شده صحیح نیست');
        } else if (!Hash::check($this->accessPassword, $this->admin->access_password)) {
            $this->addError('accessPassword', 'رمز دسترسی صحیح نیست');
        } else {
            $ip = new Ip();
            $ip->title = $this->title;
            $ip->type = 'admin';
            $ip->from = implode('.', $this->allowedIp);
            $ip->save();
            session()->flash('success', 'آی پی وارد شد');
            $this->reset(['code', 'accessPassword', 'allowedIp', 'title']);
            Cache::delete('ips-verify-code-' . $this->admin->id);
            $this->emitSelf('newIpAdded');
        }
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function deleteIp(Ip $ip)
    {
        $ip->delete();
        $this->emitSelf('ipDeleted');
        session()->flash('success', 'آی پی حذف شد');
    }
    public function render()
    {
        return view('livewire.ip-management.admin-allowed-ips',[
            'allowedIps' => Ip::whereType('admin')->simplePaginate(10)
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}
