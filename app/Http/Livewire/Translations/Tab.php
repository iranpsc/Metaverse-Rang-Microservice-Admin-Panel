<?php

namespace App\Http\Livewire\Translations;

use App\Models\Translations\Modal;
use App\Models\Translations\Tab as TranslationsTab;
use Livewire\Component;

class Tab extends Component
{
    public $modal, $name;

    protected $listeners = [
        'tabAdded' => '$refresh',
        'tabDeleted' => '$refresh',
        'tabUpdated' => '$refresh'
    ];

    protected $rules = [
        'name' => 'required|max:255|alpha_dash:ascii|unique:sqlite.tabs,name',
    ];

    public function mount(Modal $modal)
    {
        $this->modal = $modal;
    }

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

        $this->emitSelf('tabAdded');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'تب اضافه شد']);
    }

    public function deleteTab(TranslationsTab $tab)
    {
        TranslationsTab::where('name', $tab->name)->delete();

        $this->emitSelf('tabDeleted');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'تب حذف شد']);
    }

    public function updated($prop)
    {
        $this->validateOnly(trim($prop));
    }

    public function render()
    {
        return view('livewire.translations.tab', [
            'tabs' => $this->modal->tabs
        ])->extends('layouts.app')->section('content');
    }
}
