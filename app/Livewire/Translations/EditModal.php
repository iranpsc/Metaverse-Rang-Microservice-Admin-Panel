<?php

namespace App\Livewire\Translations;

use Livewire\Component;
use Livewire\Attributes\Rule;

class EditModal extends Component
{
    public Modal $modal;

    #[Rule('required|string|max:2000|unique:sqlite.modals,name')]
    public $name;

    public function mount()
    {
        $this->name = $this->modal->name;
    }

    public function save()
    {
        $modals = Modal::where('name', $this->modal->name)->get();

        foreach ($modals as $modal) {
            if ($modal->is($this->modal)) {
                $this->modal->update([
                    'name' => trim($this->name),
                ]);
            } else {
                $modal->update([
                    'name' => trim($this->name),
                ]);
            }
        }

        $this->dispatch('notify', message: 'مدال ویرایش شد');
    }

    public function render()
    {
        return view('livewire.translations.edit-modal');
    }
}
