<?php

namespace App\Livewire\Translations;

use App\Models\Translations\Modal;
use App\Models\Translations\Tab as TranslationTab;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class Tab extends Component
{
    use WithPagination;

    public Modal $modal;

    #[Rule('required|alpha_dash:ascii|max:255')]
    public $name;

    public function save()
    {
        $this->validate();

        $modals = Modal::whereName($this->modal->name)->get();

        foreach ($modals as $modal) {
            $modal->tabs()->create([
                'name' => trim($this->name),
            ]);
        }

        $this->reset('name');

        $this->dispatch('notify', message: 'تب اضافه شد');
    }

    public function delete(TranslationTab $tab)
    {
        TranslationTab::where('name', $tab->name)
            ->whereHas('modal', function ($query) {
                $query->where('name', $this->modal->name);
            })
            ->delete();
    }

    #[Title('تب‌ها')]
    public function render()
    {
        return view('livewire.translations.tab', [
            'tabs' => $this->modal->tabs()->withCount([
                'fields',
                'fields as translated_fields_count' => function ($query) {
                    $query->whereNotNull('translation');
                },
            ])->paginate(10)
        ]);
    }
}
