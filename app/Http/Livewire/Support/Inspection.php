<?php

namespace App\Http\Livewire\Support;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Ticket;
use App\Notifications\TicketResponded;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Inspection extends Component
{
    use WithFileUploads, WithPagination;

    public $response, $attachment, $department, $importance;

    protected $paginationTheme = 'bootstrap';

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
            $path = env('FTP_ENDPOINT') . $this->attachment->store('/tickets/ticketResponses');
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

        $this->dispatchBrowserEvent('resourceModified', ['message' => 'پاسخ تیکت ارسال شد']);
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
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'تیکت به واحد مورد نظر ارجاع داده شد']);
    }
    public function render()
    {
        return view('livewire.support.inspection', [
            'tickets' => Ticket::with('responses')
                ->whereIn('department', ['technical_support'])
                ->orderBy('status')
                ->orderBy('importance', 'desc')->paginate(10)
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}
