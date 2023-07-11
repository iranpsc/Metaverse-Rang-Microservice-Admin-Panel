<?php

namespace App\Http\Livewire\Translations;

use App\Models\Translations\Field as TranslationsField;
use App\Models\Translations\Tab;
use Livewire\Component;

class Field extends Component
{
    public $tab, $name, $translation;

    protected $listeners = [
        'fieldAdded' => '$refresh',
        'fieldDeleted' => '$refresh',
        'fieldUpdated' => '$refresh'
    ];

    protected $rules = [
        'name' => 'required|string|max:500|unique:sqlite.fields,name',
        'translation' => 'required|string|max:500',
    ];

    public function mount(Tab $tab)
    {
        $this->tab = $tab;
    }

    public function save()
    {
        $this->validate();

        $this->tab->fields()->create([
            'name' => $this->name,
            'translation' => $this->translation,
        ]);

        $tabs = Tab::whereNot('id', $this->tab->id)->get();

        foreach ($tabs as $tab) {
            $tab->fields()->create([
                'name' => $this->name,
            ]);
        }

        $this->reset('name', 'translation');

        $this->emitSelf('fieldAdded');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'فیلد اضافه شد']);
    }

    public function deleteField(TranslationsField $field)
    {
        TranslationsField::where('name', $field->name)->delete();
        $this->emitSelf('fieldDeleted');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'فیلد حذف شد']);
    }

    public function render()
    {
        return view('livewire.translations.field', [
            'fields' => $this->tab->fields
        ])->extends('layouts.app')->section('content');
    }
}
