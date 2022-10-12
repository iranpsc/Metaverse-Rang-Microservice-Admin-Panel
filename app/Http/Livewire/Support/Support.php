<?php

namespace App\Http\Livewire\Support;

use Livewire\Component;
use App\Models\Ticket;

class Support extends Component
{
    public $tickets;

    public function mount() {
        $this->tickets = Ticket::with('responses')->orderBy('status')->orderBy('importance' , 'desc')->get();
        $this->tickets = $this->tickets->reject(function($ticket) {
            return ! $ticket->department;
        });
    }

    public function render()
    {
        return view('livewire.support.support');
    }
}
