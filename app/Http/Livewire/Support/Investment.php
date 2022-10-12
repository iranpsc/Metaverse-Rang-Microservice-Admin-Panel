<?php

namespace App\Http\Livewire\Support;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Ticket;
use App\Notifications\TicketResponded;

class Investment extends Component
{
    use WithFileUploads;

    public $tickets, $response, $attachment, $department, $importance;

    public function mount($tickets) {
        $this->tickets = $tickets->reject(function($ticket) {
            return $ticket->department != 'investment';
        });
    }

    protected $rules = [
        'response' => 'required|string',
        'attachment' => 'nullable|file|mimes:pdf,png,jpeg,jpg',
    ];

    protected $messages = [
        'response.required' => 'متن پاسخ را وارد کنید',
        'attachment.mimes' => 'فرمت فایل انتخاب شده معتبر نمی باشد'
    ];

    public function updated($propertyName) {
        $this->validateOnly($propertyName);
    }

    public function sendResponse(Ticket $ticket) {
        $this->validate();

        if($this->attachment) {
            $path = env('FTP_ENDPOINT') . $this->attachment->store('/tickets/ticketResponses/' . $ticket->id);
        } else {
            $path = "";
        }

        $ticket->responses()->create([
            'response' => $this->response,
            'attachment' => $path,
        ]);

        $ticket->update([
            'responser_name' => auth()->user()->name,
            'status' => 1,
        ]);

        $message = 'به تیکت شما به شماره ' . $ticket->code . 'پاسخ داده شد';

        $ticket->sender->notify(new TicketResponded($message));

        session()->flash('success', 'پاسخ تیکت ارسال شد');
    }

    public function sendTo(Ticket $ticket) {
        $this->validate([
            'department' => 'required',
            'importance' => 'required'
        ]);
        $ticket->update([
            'department' => $this->department,
            'importance' => $this->importance
        ]);
        session()->flash('success', 'تیکت به واحد مورد نظر ارجا داده شد');
    }
    public function render()
    {
        return view('livewire.support.investment');
    }
}
