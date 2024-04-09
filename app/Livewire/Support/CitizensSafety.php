<?php

namespace App\Livewire\Support;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Ticket;
use App\Notifications\TicketResponded;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class CitizensSafety extends Component
{
    use WithFileUploads, WithPagination;

    public $response, $attachment, $department, $importance, $search;

    protected $rules = [
        'response' => 'required|string',
        'attachment' => 'nullable|file|mimes:pdf,png,jpeg,jpg',
    ];

    public function sendResponse(Ticket $ticket)
    {
        $this->validate();

        if ($this->attachment) {
            $path = $this->attachment->store('/tickets/ticketResponses');
        } else {
            $path = "";
        }

        $ticket->responses()->create([
            'response' => $this->response,
            'attachment' => $path,
            'responser_name' => Auth::user()->name,
            'responser_id' => Auth::id(),
        ]);

        $ticket->update(['status' => 1]);

        $message = 'به تیکت شما به شماره ' . $ticket->code . 'پاسخ داده شد';

        $ticket->sender->notify(new TicketResponded($message));

        $this->dispatch('notify', message: 'پاسخ تیکت ارسال شد');
    }

    public function sendTo(Ticket $ticket)
    {
        $this->validate([
            'department' => 'required',
            'importance' => 'required'
        ]);

        $ticket->update([
            'department' => $this->department,
            'importance' => $this->importance
        ]);

        $this->dispatch('notify', message: 'تیکت به واحد مورد نظر ارجاع داده شد');
    }

    #[Title('امنیت شهروندان')]
    public function render()
    {
        return view('livewire.support.citizens-safety', [
            'tickets' => Ticket::with('responses')
                ->whereIn('department', ['citizens_safety'])
                ->orderBy('status')
                ->orderBy('importance', 'desc')->paginate(10)
        ]);
    }
}
