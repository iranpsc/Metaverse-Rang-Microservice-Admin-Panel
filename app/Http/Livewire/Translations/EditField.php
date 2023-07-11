<?php

namespace App\Http\Livewire\Translations;

use App\Models\Translations\Field;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EditField extends Component
{
    public $field, $name, $translation;

    public function mount()
    {
        $this->name = $this->field->name;
        $this->translation = $this->field->translation;
    }

    public function save()
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sqlite.fields')->ignore($this->field, 'name'),
            ],
            'translation' => 'required|string|max:255',
        ]);


        $fields = Field::where('name', $this->field->name)->get();

        foreach ($fields as $field) {

            if($field->is($this->field))
            {
                $this->field->update([
                    'name' => trim($this->name),
                    'translation' => $this->translation
                ]);
            } else {
                $field->update([
                    'name' => trim($this->name),
                ]);
            }

        }

        $this->emitUp('fieldUpdated');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'فیلد ویرایش شد']);
    }

    public function render()
    {
        return view('livewire.translations.edit-field');
    }
}
