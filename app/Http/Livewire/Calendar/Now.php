<?php

namespace App\Http\Livewire\Calendar;

use App\Models\Calendar;
use Livewire\Component;

class Now extends Component
{
    private $events, $title, $content, $image, $start_date;

    public function mount($events) {
        $this->events = $events->reject(function($event) {
            return $event->start_date > now() || $event->end_date < now();
        });
    }
    public function update($id) {
        $event = Calendar::find($id);
    }
    public function render()
    {
        return view('livewire.calendar.now', ['events' => $this->events]);
    }
}
