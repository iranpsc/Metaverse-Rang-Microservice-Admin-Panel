<?php

namespace App\Http\Livewire\Dynasty;

use Livewire\Component;

class EditMessages extends Component
{
    public $message, $type, $message_content;

    public function mount($message) {
        $this->message = $message;
        $this->type = $message->type;
        $this->message_content = $message->message;
    }


    protected $rules = [
        'message_content' => 'required|string',
    ];

    protected $messages = [
        'message_content.required' => 'متن پیام را وارد کنید'
    ];


    public function edit()
    {
        $this->validate();
        $this->message->update([
            'type' => $this->type,
            'message' => $this->message_content
        ]);
        session()->flash('success', 'بروزرسانی انجام شد');
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.dynasty.edit-messages');
    }
}
