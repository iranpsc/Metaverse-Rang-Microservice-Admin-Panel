<?php

namespace App\Http\Livewire\Calendar;

use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateEvent extends Component
{
    use WithFileUploads;
    public $event, $title, $content, $image, $start_date, $end_date, $start_time, $end_time, $color;

    protected $rules = [
        'title' => 'required|string|min:2',
        'content' => 'required|string|min:2',
        // 'image' => 'nullable|image|mimes:jpg,jpeg,png,bmp',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'start_time' => 'required',
        'end_time' => 'required',
        'color' => 'required|string'
    ];

    protected $messages = [
        'title.required' => 'عنوان را وارد کنید',
        'content.required' => 'متن را وارد کنید',
        'start_date.required' => 'تاریخ شروع را وارد کنید',
        'end_date.required' => 'تاریخ پایان را وارد کنید',
        'start_time.required' => 'ساعت شروع را وارد کنید',
        'end_time.required' => 'ساعت پایان را وارد کنید',
        'color.required' => 'رنگ را وارد کنید'
    ];

    public function mount($event) {

        $this->title = $event->title;
        $this->content = $event->content;
        $this->image = $event->image;
        $this->start_date = $event->start_date;
        $this->end_date = $event->end_date;
        $this->start_time = $event->start_time;
        $this->end_time = $event->end_time;
        $this->color = $event->color;
    }
    public function update() {
        $this->validate();

        $this->event->update([
            'title' => $this->title,
            'content' => $this->content,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'color' => $this->color,
        ]);

        if($this->image) {
            $url = env('FTP_ENDPOINT') . $this->image->store('public/calendar');
            if($this->event->image) {
                $this->event->image->update(['url' => $url]);
            } else {
                $this->event->image()->create(['url' => $url]);
            }
        }
        session()->flash('success', 'وقعه ثبت شد.');
        $this->emitUp('eventUpdated');
    }
    public function render()
    {
        return view('livewire.calendar.update-event');
    }
}
