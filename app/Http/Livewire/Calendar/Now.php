<?php

namespace App\Http\Livewire\Calendar;

use App\Models\Calendar;
use Livewire\Component;
use Morilog\Jalali\Jalalian;

class Now extends Component
{
    private $events, $title, $content, $image, $start_date;

    public function mount($events) {
        $this->events = $events->reject(function($event) {
            return $event->start_date > Jalalian::now() || $event->end_date < Jalalian::now();
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
