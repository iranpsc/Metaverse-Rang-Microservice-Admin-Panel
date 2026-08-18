<?php

namespace App\Livewire\Dynasty;

use App\Models\Dynasty\DynastyMessage;
use Livewire\Component;
use Livewire\Attributes\Title;

class DynastyMessages extends Component
{
    public $type, $content;

    protected $rules = [
        'type' => 'required|in:requester_confirmation_message,reciever_message,reciever_accept_message,requester_accept_message|unique:dynasty_messages',
        'content' => 'required|string',
    ];

    public function save()
    {
        $this->validate();
        DynastyMessage::create([
            'type' => $this->type,
            'message' => $this->content
        ]);
        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
        $this->reset();
    }

    public function delete($id)
    {
        DynastyMessage::destroy($id);
    }

    #[Title('پیام های سلسله')]
    public function render()
    {
        return view('livewire.dynasty.dynasty-messages', [
            'dynastyMessages' => DynastyMessage::all()
        ]);
    }
}
