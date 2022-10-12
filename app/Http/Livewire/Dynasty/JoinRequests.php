<?php

namespace App\Http\Livewire\Dynasty;

use App\Models\Dynasty\JoinRequest;
use Livewire\Component;

class JoinRequests extends Component
{
    public $joinRequests;

    public function mount()
    {
        $this->joinRequests = JoinRequest::all();
    }

    public function render()
    {
        return view('livewire.dynasty.join-requests');
    }
}
