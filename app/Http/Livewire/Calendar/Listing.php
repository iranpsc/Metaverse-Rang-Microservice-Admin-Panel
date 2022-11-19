<?php

namespace App\Http\Livewire\Calendar;

use Faker\Extension\ColorExtension;
use Livewire\Component;
use App\Models\Calendar;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Listing extends Component
{
    use WithFileUploads, WithPagination;

    public $title,
            $content,
            $image,
            $start_date,
            $end_date,
            $start_time,
            $end_time,
            $views,
            $color;
    private $events;

    protected $listeners = [
        'eventCreated' => '$refresh',
        'eventUpdated' => '$refresh',
        'eventDeleted' => '$refresh',
        'deleteEvent'  => 'delete',
    ];

    protected $rules = [
        'title' => 'required|string|min:2',
        'content' => 'required|string|min:2',
        'image' => 'required|image|mimes:jpg,jpeg,png',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'start_time' => 'required',
        'end_time' => 'required',
        'color' => 'required|string'
    ];

    protected $messages = [
        'title.required' => 'عنوان را وارد کنید',
        'content.required' => 'متن را وارد کنید',
        'image.required' => 'عکس را وارد کنید',
        'start_date.required' => 'تاریخ شروع را وارد کنید',
        'end_date.required' => 'تاریخ پایان را وارد کنید',
        'start_time.required' => 'ساعت شروع را وارد کنید',
        'end_time.required' => 'ساعت پایان را وارد کنید',
        'color.required' => 'رنگ را وارد کنید'
    ];

    public function mount() {
        $this->events = Calendar::with('likes' , 'dislikes', 'image' )->lazy();
    }


    public function save() {
        $this->validate();
        $event = Calendar::create([
            'title' => $this->title,
            'content' => $this->content,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'color' => $this->color,
        ]);
        $url = env('FTP_ENDPOINT') . $this->image->store('public/calendar');
        $event->image()->create(['url' => $url]);
        session()->flash('success', 'وقعه ثبت شد.');
        $this->emitSelf('eventCreated');
    }

    public function updated($prop) {
        $this->validateOnly($prop);
    }

    public function delete(Calendar $calendar) {
        $calendar->image->delete();
        $calendar->delete();
        $this->emitSelf('eventDeleted');
    }
    public function render()
    {
        return view('livewire.calendar.listing', [
            'events' => $this->events
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
