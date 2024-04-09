<?php

namespace App\Livewire\IpManagement;

use Livewire\Component;
use App\Models\Ip;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

class AdminAllowedIps extends Component
{
    use WithPagination, SendsVerificationSms;

    public $allowedIp = [], $title;

    protected $rules = [
        'title' => 'required|string',
        'allowedIp.*' => 'required|integer|min:0|max:255',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function save()
    {
        $this->validate();

        $ip = new Ip();
        $ip->title = $this->title;
        $ip->type = 'admin';
        $ip->from = implode('.', $this->allowedIp);
        $ip->save();
        $this->resetExcept('admin');
        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }

    public function delete(Ip $ip)
    {
        $ip->delete();
    }

    #[Title('مدیریت آی‌پی‌ها')]
    public function render()
    {
        return view('livewire.ip-management.admin-allowed-ips', [
            'ips' => Ip::whereType('admin')->paginate(10)
        ]);
    }
}
