<?php

namespace App\Http\Livewire\IpManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Ip;
use App\Traits\SendsVerificationSms;
use Livewire\WithPagination;

class ApiAllowedIps extends Component
{
    use WithPagination, SendsVerificationSms;

    protected $paginationTheme = 'bootstrap';

    public $allowedIp = [], $title;

    protected $listeners = [
        'newIpAdded' => '$refresh',
        'deleteApiIp' => 'deleteIp',
        'ipDeleted' => '$refresh',
    ];

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
        $ip->type = 'api';
        $ip->from = implode('.', $this->allowedIp);
        $ip->save();
        $this->resetExcept('admin');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'اطلاعات با موفقیت ثبت شد']);
        $this->emitSelf('newIpAdded');
    }

    public function deleteIp(Ip $ip)
    {
        $ip->delete();
        $this->emitSelf('ipDeleted');
    }

    public function render()
    {
        return view('livewire.ip-management.api-allowed-ips', [
            'allowedIps' => Ip::whereType('api')->simplePaginate(10),
        ]);
    }
}
