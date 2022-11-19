<?php

namespace App\Http\Livewire\Calendar;

use Livewire\Component;

class AgoEvents extends Component
{
    private $events;

    public function mount($events) {
        $this->events = $events->reject(function($event) {
            return $event->end_date > now();
        });
    }
    public function render()
    {
        return view('livewire.calendar.ago-events', ['events' => $this->events]);
    }
}
