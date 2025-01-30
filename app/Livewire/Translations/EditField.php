<?php

namespace App\Livewire\Translations;

use App\Models\Translations\Field;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EditField extends Component
{
    public Field $field;

    public $name, $translation, $unique_id;

    public function mount()
    {
        $this->name = $this->field->name;
        $this->translation = $this->field->translation;
        $this->unique_id = $this->field->unique_id;
    }

    public function save()
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:2000',
                Rule::unique('sqlite.fields')->ignore($this->field->name, 'name'),
            ],
            'translation' => 'required|string|max:2000',
            'unique_id' => [
                'required',
                'integer',
                Rule::unique('sqlite.fields')->ignore($this->field->unique_id, 'unique_id'),
            ]
        ]);

        $fields = Field::where('name', $this->field->name)->get();

        foreach ($fields as $field) {
            if ($field->is($this->field)) {
                $this->field->update([
                    'unique_id' => $this->unique_id,
                    'name' => trim($this->name),
                    'translation' => $this->translation,
                ]);
            } else {
                $field->update([
                    'unique_id' => $this->unique_id,
                    'name' => trim($this->name),
                ]);
            }
        }

        $this->dispatch('notify', message: 'فیلد ویرایش شد');
    }

    public function render()
    {
        return view('livewire.translations.edit-field');
    }
}
