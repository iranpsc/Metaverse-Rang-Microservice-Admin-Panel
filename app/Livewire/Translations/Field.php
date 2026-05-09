<?php

namespace App\Livewire\Translations;

use App\Models\Translations\Field as TranslationField;
use App\Models\Translations\Tab;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class Field extends Component
{
    use WithPagination;

    public Tab $tab;

    public $value;

    protected function rules()
    {
        return [
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

        // Get the latest unique_id and increment it by 1
        $latestUniqueId = TranslationField::max('unique_id');
        $latestUniqueId = $latestUniqueId ? $latestUniqueId + 1 : 1;

        $this->tab->fields()->create([
            'unique_id' => $latestUniqueId,
            'translation' => $this->value,
        ]);

        $tabs = Tab::whereNot('id', $this->tab->id)->where('name', $this->tab->name)->get();

        foreach ($tabs as $tab) {
            $tab->fields()->create([
                'unique_id' => $latestUniqueId,
            ]);
        }

        $this->reset('value');

        $this->dispatch('notify', message: 'فیلد اضافه شد');
    }

    public function delete(TranslationField $field)
    {
        // Delete all fields with the same unique_id
        TranslationField::where('unique_id', $field->unique_id)->delete();
        $this->dispatch('notify', message: 'فیلد حذف شد');
    }

    #[Title('فیلدها')]
    public function render()
    {
        return view('livewire.translations.field', [
            'fields' => TranslationField::where('tab_id', $this->tab->id)->paginate(10)
        ]);
    }
}
