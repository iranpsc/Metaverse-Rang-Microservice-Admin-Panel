<?php

namespace App\Livewire\Translations;

use Livewire\Component;
use Livewire\Attributes\Rule;
use App\Models\Translations\Modal as TranslationModal;

class EditModal extends Component
{
    public TranslationModal $modal;

    #[Rule('required|string|max:2000|unique:sqlite.modals,name')]
    public $name;

    public function mount()
    {
        $this->name = $this->modal->name;
    }

    public function update()
    {
        $modals = TranslationModal::where('name', $this->modal->name)->get();

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
