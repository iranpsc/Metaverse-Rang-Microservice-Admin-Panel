<?php

namespace App\Livewire\Support;

use App\Models\Ticket;
use App\Notifications\TicketResponded;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

class TechnicalSupport extends Component
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
        $ticket->update([
            'department' => $this->department,
            'importance' => $this->importance
        ]);

        $this->dispatch('notify', message: 'تیکت به واحد مورد نظر ارجاع داده شد');
    }

    #[Title('پشتیبانی فنی')]
    public function render()
    {
        return view('livewire.support.technical-support', [
            'tickets' => Ticket::with('responses')
                ->whereIn('department', ['technical_support'])
                ->orderBy('status')
                ->orderBy('importance', 'desc')->paginate(10)
        ]);
    }
}
