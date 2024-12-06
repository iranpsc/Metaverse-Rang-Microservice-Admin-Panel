<?php

namespace App\Livewire\Translations;

use App\Models\Translations\Field as TranslationField;
use App\Models\Translations\Tab;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Str;

class Field extends Component
{
    use WithPagination;

    public Tab $tab;

    public $name, $value, $unique_id;

    protected function rules()
    {
        return [
            'unique_id' => 'required|alpha_dash:ascii|min:40|max:255|unique:sqlite.fields,unique_id,NULL,id,name,' . $this->name,
            'name' => 'required|string|max:2000',
            'value' => 'required|string|max:2000',
        ];
    }

    public function mount()
    {
        $this->tab = $this->tab->load(['modal', 'modal.translation']);
    }

    public function save()
    {
        $this->validate();

        $this->tab->fields()->create([
            'unique_id' => Str::slug(trim($this->unique_id)),
            'name' => $this->name,
            'translation' => $this->value,
        ]);

        $tabs = Tab::whereNot('id', $this->tab->id)->where('name', $this->tab->name)->get();

        foreach ($tabs as $tab) {
            $tab->fields()->create([
                'name' => $this->name,
            ]);
        }

        $this->reset('name', 'value');

        $this->dispatch('notify', message: 'فیلد اضافه شد');
    }

    public function delete(TranslationField $field)
    {
        TranslationField::where('name', $field->name)->delete();
    }

    #[Title('فیلدها')]
    public function render()
    {
        return view('livewire.translations.field', [
            'fields' => TranslationField::where('tab_id', $this->tab->id)->paginate(10)
        ]);
    }
}
