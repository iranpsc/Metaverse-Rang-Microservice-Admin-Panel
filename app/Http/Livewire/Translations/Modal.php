<?php

namespace App\Http\Livewire\Translations;

use App\Models\Translations\Modal as TranslationModal;
use App\Models\Translations\Translation;
use Livewire\Component;
use Livewire\WithPagination;

class Modal extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $translation, $name;

    protected $listeners = [
        'modalAdded' => '$refresh',
        'modalDeleted' => '$refresh',
        'modalUpdated' => '$refresh'
    ];

    protected $rules = [
        'name' => 'required|alpha_dash:ascii|max:255|unique:sqlite.modals,name',
    ];

    public function mount(Translation $translation)
    {
        $this->translation = $translation;
    }

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

        $this->emitSelf('modalAdded');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'مدال اضافه شد']);
    }

    public function deleteModal(TranslationModal $modal)
    {
        TranslationModal::where('name', $modal->name)->delete();

        $this->emitSelf('modalDeleted');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'مدال حذف شد']);
    }

    public function updated($prop)
    {
        $this->validateOnly(trim($prop));
    }

    public function render()
    {
        return view('livewire.translations.modal', [
            'modals' => $this->translation->modals()->simplePaginate(10)
        ]);
    }
}
