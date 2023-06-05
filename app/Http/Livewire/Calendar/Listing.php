<?php

namespace App\Http\Livewire\Calendar;

use Livewire\Component;
use App\Models\Calendar;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Listing extends Component
{
    use WithFileUploads, WithPagination, SendsVerificationSms;

    public $title, $content, $image, $start_date, $end_date, $is_version = false, $color;
    public $btn_name, $btn_link, $version_title, $start_time, $end_time;

    protected $listeners = [
        'eventCreated' => '$refresh',
        'eventUpdated' => '$refresh',
        'eventDeleted' => '$refresh',
        'deleteEvent'  => 'delete',
    ];

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
        'version_title' => 'nullable|string|min:2|max:255',
        'is_version' => 'nullable|boolean',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function save()
    {
        $this->validate();

        Calendar::create([
            'title' => $this->title,
            'content' => $this->content,
            'starts_at' => $this->is_version ? $this->start_date : $this->start_date . ' ' . $this->start_time,
            'ends_at' => $this->is_version ? null : $this->end_date . ' ' . $this->end_time,
            'color' => $this->color ?? ' ',
            'writer' => Auth::guard('admin')->user()->name,
            'btn_name' => $this->btn_name,
            'btn_link' => $this->btn_link,
            'version_title' => $this->version_title,
            'is_version' => $this->is_version,
            'image' => $this->image ? url('uploads/' . $this->image->store('calendars', 'public')) : '',
        ]);

        $this->resetExcept('admin');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'وقعه ثبت شد.']);
        $this->emitSelf('eventCreated');
    }

    public function delete(Calendar $calendar)
    {
        $calendar->delete();
        $this->emitSelf('eventDeleted');
    }

    public function render()
    {
        return view('livewire.calendar.listing', [
            'events' => Calendar::with('interactions')->latest('starts_at')->simplePaginate(10)
        ])->extends('layouts.app')->section('content');
    }
}
