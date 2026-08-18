<?php

namespace App\Livewire\IpManagement;

use App\Models\Ip;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class WhiteListRequests extends Component
{
    use WithPagination;

    public function approve(Ip $ip)
    {
        $ip->update(['blocked' => 0]);
        $this->dispatch('notify', message: 'آی‌پی مورد نظر با موفقیت تایید شد');
    }

    public function deny(Ip $ip)
    {
        $ip->update(['blocked' => 1]);
        $this->dispatch('notify', message: 'آی‌پی مورد نظر با موفقیت رد شد');
    }

    #[Title('درخواست‌های لیست سفید')]
    public function render()
    {
        return view('livewire.ip-management.white-list-requests', [
            'ips' => Ip::where('type', 'api')->where('blocked', 1)->paginate(10)
        ]);
    }
}
