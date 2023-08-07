<?php

namespace App\Http\Livewire\Calendar;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;

class Update extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public $event, $title, $content, $image, $start_date, $end_date, $color;
    public $btn_name, $btn_link, $start_time, $end_time;

    protected $rules = [
        'title' => 'required|string|min:2|max:255',
        'content' => 'required|string|min:2|max:5000',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2024',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date',
        'start_time' => 'nullable|string|min:2|max:255',
        'end_time' => 'nullable|string|min:2|max:255',
        'color' => 'nullable|string',
        'btn_name' => 'nullable|string|min:2|max:255',
        'btn_link' => 'nullable|string|min:2|max:255',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount($event) {

        $this->title = $event->title;
        $this->content = $event->content;
        $this->color = $event->color;
        $this->btn_name = $event->btn_name;
        $this->btn_link = $event->btn_link;
        $this->start_date = $event->starts_at->format('Y-m-d');
        $this->end_date = $event->is_version ? null : $event->ends_at->format('Y-m-d');
        $this->start_time = $event->is_version ? null : $event->starts_at->format('H:i');
        $this->end_time = $event->is_version ? null : $event->ends_at->format('H:i');
        $this->event = $event;

        $this->admin = Auth::guard('admin')->user();
    }

    public function save() {
        $this->validate();

        $this->event->update([
            'title' => $this->title,
            'content' => $this->content,
            'starts_at' => $this->event->is_version ? $this->start_date : $this->start_date . ' ' . $this->start_time,
            'ends_at' => $this->event->is_version ? null : $this->end_date . ' ' . $this->end_time,
            'color' => $this->color ?? ' ',
            'btn_name' => $this->btn_name,
            'btn_link' => $this->btn_link,
            'image' => $this->image ? $this->image->store('events', 'public') : $this->event->image,
        ]);
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'وقعه ویرایش شد']);
        $this->emitUp('eventUpdated');
    }
    public function render()
    {
        return view('livewire.calendar.update');
    }
}
