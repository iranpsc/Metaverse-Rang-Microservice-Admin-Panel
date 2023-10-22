<?php

namespace App\Http\Livewire;

use App\Models\Calendar as CalendarModel;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Calendar extends LivewireComponent
{
    use WithFileUploads, WithPagination;

    public $title, $content, $image, $start_date, $end_date, $color;
    public $btn_name, $btn_link, $start_time, $end_time;

    protected $listeners = [
        'eventCreated' => '$refresh',
        'eventUpdated' => '$refresh',
        'eventDeleted' => '$refresh',
    ];

    protected $rules = [
        'title' => 'required|string|min:2|max:255',
        'content' => 'required|string|min:2|max:5000',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2024',
        'start_date' => 'required|shamsi_date',
        'end_date' => 'required|shamsi_date',
        'start_time' => 'required|string|min:2|max:255',
        'end_time' => 'required|string|min:2|max:255',
        'color' => 'nullable|string',
        'btn_name' => 'nullable|string|min:2|max:255',
        'btn_link' => 'nullable|string|min:2|max:255',
    ];

    public function store()
    {
        $this->validate(array_merge($this->rules, $this->getVerficationRules()));

        $image = $this->image ? $this->image->store('calendars', 'public') : 'noimage.png';

        CalendarModel::create([
            'title' => $this->title,
            'content' => $this->content,
            'starts_at' => $this->start_date . ' ' . $this->start_time,
            'ends_at' => $this->end_date . ' ' . $this->end_time,
            'color' => $this->color ?? '#000000',
            'writer' => $this->admin->name,
            'btn_name' => $this->btn_name,
            'btn_link' => $this->btn_link,
            'image' => url('uploads/' . $image),
        ]);

        $this->emitSelf('eventCreated');
        $this->dispatchBrowserEvent('closeCreateModal');
        $this->resetExcept('admin');
    }

    public function edit($id)
    {
        $event = CalendarModel::findOrFail($id);

        $this->title = $event->title;
        $this->content = $event->content;
        $this->start_date = $event->starts_at->format('Y-m-d');
        $this->end_date = $event->ends_at->format('Y-m-d');
        $this->start_time = $event->starts_at->format('H:i');
        $this->end_time = $event->ends_at->format('H:i');
        $this->color = $event->color;
        $this->btn_name = $event->btn_name;
        $this->btn_link = $event->btn_link;

        $this->dispatchBrowserEvent('openEditModal', [
            'id' => $event->id,
            'content' => $this->content,
        ]);
    }

    public function update($id)
    {
        $this->validate(array_merge($this->rules, $this->getVerficationRules()));

        $event = CalendarModel::findOrFail($id);

        $image = $this->image ? $this->image->store('calendars', 'public') : $event->image;

        $event->update([
            'title' => $this->title,
            'content' => $this->content,
            'starts_at' => $this->start_date . ' ' . $this->start_time,
            'ends_at' => $this->end_date . ' ' . $this->end_time,
            'color' => $this->color ?? '#000000',
            'btn_name' => $this->btn_name,
            'btn_link' => $this->btn_link,
            'image' => url('uploads/' . $image),
        ]);

        $this->emitSelf('eventUpdated');
        $this->dispatchBrowserEvent('closeEditModal');
        $this->resetExcept('admin');
    }

    public function delete($id)
    {
        $event = CalendarModel::findOrFail($id);
        $event->delete();
        $this->emitSelf('eventDeleted');
    }

    public function render()
    {
        return view('livewire.calendar', [
            'events' => CalendarModel::event()
                ->with(['interactions', 'views'])
                ->latest('starts_at')
                ->paginate(10),
        ]);
    }
}
