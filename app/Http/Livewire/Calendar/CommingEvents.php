<?php

namespace App\Http\Livewire\Calendar;

use Livewire\Component;

class CommingEvents extends Component
{

    private $events;

    public function mount($events) {
        $this->events = $events->reject(function($event) {
            return $event->start_date < now();
        });
    }
    public function render()
    {
        return view('livewire.calendar.comming-events', ['events' => $this->events]);
    }
}
