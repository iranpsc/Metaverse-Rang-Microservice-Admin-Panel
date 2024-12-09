<?php

namespace App\Livewire\Translations;

use Livewire\Attributes\Rule;
use Livewire\Component;
use App\Models\Translations\Tab as TranslationTab;

class EditTab extends Component
{
    public TranslationTab $tab;

    #[Rule('required|string|max:2000|unique:sqlite.tabs,name')]
    public $name;

    public function mount()
    {
        $this->name = $this->tab->name;
    }

    public function update()
    {
        $tabs = TranslationTab::where('name', $this->tab->name)
            ->whereHas('modal', function ($query) {
                $query->where('name', $this->tab->modal->name);
            })
            ->get();

        foreach ($tabs as $tab) {
            if ($tab->is($this->tab)) {
                $this->tab->update([
                    'name' => trim($this->name),
                ]);
            } else {
                $tab->update([
                    'name' => trim($this->name),
                ]);
            }
        }

        $this->dispatch('notify', message: 'تب ویرایش شد');
    }

    public function render()
    {
        return view('livewire.translations.edit-tab');
    }
}
