<?php

namespace App\Http\Livewire\IpManagement;

use App\Models\Ip;
use Livewire\Component;
use Livewire\WithPagination;

class WhiteListRequests extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'ipApproved' => '$refresh',
        'ipDenied' => '$refresh',
        'deleted' => '$refresh',
    ];

    public function approve($id)
    {
    }

    public function deny($id)
    {
        $ip = Ip::findOrFail($id);
        $ip->update(['blocked' => 1]);
        $this->emitSelf('ipDenied');
    }

    public function render()
    {
        return view('livewire.ip-management.white-list-requests', [
            'pageTitle' => 'درخواست های رفع مسدودیت',
            'ips' => Ip::where('type', 'api')->where('blocked', 1)->simplePaginate(10)
        ])->extends('layouts.app')->section('content');
    }
}
