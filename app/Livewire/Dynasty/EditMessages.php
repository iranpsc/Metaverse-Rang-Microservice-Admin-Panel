<?php

namespace App\Livewire\Dynasty;

use App\Models\Dynasty\DynastyMessage;
use Livewire\Attributes\Rule;
use Livewire\Component;

class EditMessages extends Component
{
    public DynastyMessage $message;

    #[Rule('required|string')]
    public $content;

    public function save()
    {
        $this->validate();
        $this->message->update([
            'message' => $this->content
        ]);
        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }
}
