<?php

namespace App\Http\Livewire\Dynasty;

use App\Models\Dynasty\DynastyMessage;
use Livewire\Component;

class DynastyMessages extends Component
{
    public $type, $content;

    protected $listeners = [
        'deleteDynastyMessage' => 'delete',
        'messageCreated' => '$refresh',
        'messageUpdated' => '$refresh',
        'messageDeleted' => '$refresh',
    ];

    protected $rules = [
        'type' => 'required|in:requester_confirmation_message,reciever_message,reciever_accept_message,requester_accept_message|unique:dynasty_messages',
        'content' => 'required|string',
    ];

    protected $messages = [
        'type.required' => 'نوع پیام را مشخص کنید',
        'type.in' => 'انتخاب نا معتبر',
        'type.unique' => 'این پیام قبلا تعریف شده است',
        'content.required' => 'متن پیام را وارد کنید'
    ];

    public function save()
    {
        $this->validate();
        DynastyMessage::create([
            'type' => $this->type,
            'message' => $this->content
        ]);
        session()->flash('success', 'پیام ایجاد گردید');
        $this->reset();
        $this->emitSelf('messageCreated');
    }

    public function updated($prop) {
        $this->validateOnly($prop);
    }

    public function delete($id)
    {
        DynastyMessage::destroy($id);
        $this->emitSelf('messageDeleted');
    }


    public function render()
    {
        return view('livewire.dynasty.dynasty-messages', [
            'dynastyMessages' => DynastyMessage::lazy()
        ]);
    }
}
