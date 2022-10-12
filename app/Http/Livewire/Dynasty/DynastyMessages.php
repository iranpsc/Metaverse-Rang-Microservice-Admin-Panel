<?php

namespace App\Http\Livewire\Dynasty;

use App\Models\Dynasty\DynastyMessage;
use Livewire\Component;

class DynastyMessages extends Component
{
    public $dynastyMessages;
    public DynastyMessage $message;

    public function mount()
    {
        $this->dynastyMessages = DynastyMessage::all();
    }

    public function __construct()
    {
        $this->message = new DynastyMessage;
    }

    protected $rules = [
        'message.type' => 'required|in:requester,reciever,requester_accept,reciever_accept,dynasty_prize',
        'message.message' => 'required|string',
    ];

    protected $messages = [
        'message.type.required' => 'نوع پیام را مشخص کنید',
        'message.type.in' => 'انتخاب نا معتبر',
        'message.message.required' => 'متن پیام را وارد کنید'
    ];

    public function save()
    {
        $this->validate();
        $this->message->save();
        $this->resetExcept($this->dynastyMessages);
        session()->flash('success', 'پیام ایجاد گردید');
        $this->dynastyMessages = DynastyMessage::all();
    }

    public function updated($prop) {
        $this->validateOnly($prop);
    }

    public function delete(DynastyMessage $dynastyMessage)
    {
       $dynastyMessage->delete();
        session()->flash('success', 'پیام حذف شد');
        $this->dynastyMessages = DynastyMessage::all();
    }


    public function render()
    {
        return view('livewire.dynasty.dynasty-messages');
    }
}
