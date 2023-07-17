<?php

namespace App\Http\Livewire\Dynasty;

use Livewire\Component;

class EditMessages extends Component
{
    public $message, $content;

    public function mount($message) {
        $this->message = $message;
        $this->content = $message->message;
    }

    protected $rules = [
        'content' => 'required|string',
    ];

    public function save()
    {
        $this->validate();
        $this->message->update([
            'message' => $this->content
        ]);
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'اطلاعات با موفقیت ثبت شد']);
        $this->emitUp('messageUpdated');
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
