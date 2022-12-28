<?php

namespace App\Http\Livewire\Challenge;

use Livewire\Component;

class ShowQuestion extends Component
{
    public $title, $code, $question;

    public function mount($question)
    {
        $this->question = $question;
        $this->title = $question->title;
        $this->code = $question->code;
    }

    public function render()
    {
        return view('livewire.challenge.show-question');
    }
}
