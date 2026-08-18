<?php

namespace App\Livewire\Translations;

use App\Models\Translations\Modal as TranslationModal;
use App\Models\Translations\Translation;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Modal extends Component
{
    use WithPagination;

    public Translation $translation;

    #[Rule('required|alpha_dash:ascii|max:255|unique:sqlite.modals,name')]
    public $name;

    public function save()
    {
        $this->validate();

        $translations = Translation::all();

        foreach ($translations as $translation) {
            $translation->modals()->create([
                'name' => trim($this->name),
            ]);
        }

        $this->reset('name');

        $this->dispatch('notify', message: 'مدال اضافه شد');
    }

    public function delete(TranslationModal $modal)
    {
        TranslationModal::where('name', $modal->name)->delete();
    }

    #[Title('مدال‌ها')]
    public function render()
    {
        return view('livewire.translations.modal', [
            'modals' => $this->translation->modals()->paginate(10)
        ]);
    }
}
