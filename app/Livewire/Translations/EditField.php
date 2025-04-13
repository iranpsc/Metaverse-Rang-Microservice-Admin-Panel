<?php

namespace App\Livewire\Translations;

use App\Models\Translations\Field;
use Livewire\Attributes\Rule;
use Livewire\Component;

class EditField extends Component
{
    public Field $field;

    #[Rule('required|string|max:5000')]
    public $translation;

    public function mount()
    {
        $this->translation = $this->field->translation;
    }

    public function save()
    {
        $this->validate();

        $this->field->update([
            'translation' => $this->translation,
        ]);

        $this->dispatch('notify', message: 'فیلد ویرایش شد');
    }

    public function render()
    {
        return view('livewire.translations.edit-field');
    }
}
