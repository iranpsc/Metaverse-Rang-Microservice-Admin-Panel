<?php

namespace App\Http\Livewire\Translations;

use App\Models\Translations\Field as TranslationsField;
use App\Models\Translations\Tab;
use Livewire\Component;
use Livewire\WithPagination;

class Field extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    
    public $tab, $name, $value;

    protected $listeners = [
        'fieldAdded' => '$refresh',
        'fieldDeleted' => '$refresh',
        'fieldUpdated' => '$refresh'
    ];

    protected $rules = [
        'name' => 'required|string|max:2000',
        'value' => 'required|string|max:2000',
    ];

    public function mount(Tab $tab)
    {
        $this->tab = $tab->load(['modal', 'modal.translation']);
    }

    public function save()
    {
        $this->validate();

        $this->tab->fields()->create([
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
            'fields' => TranslationsField::where('tab_id', $this->tab->id)->paginate(10)
        ]);
    }
}
