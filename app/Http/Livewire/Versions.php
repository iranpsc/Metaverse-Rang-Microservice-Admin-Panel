<?php

namespace App\Http\Livewire;

use App\Models\Calendar;
use Livewire\WithPagination;

class Versions extends LivewireComponent
{
    use WithPagination;

    public $title, $content, $versionTitle, $startsAt;

    protected $listeners = [
        'versionAdded' => '$refresh',
        'versionUpdated' => '$refresh',
        'versionDeleted' => '$refresh',
    ];

    protected $rules = [
        'title' => 'required|string|max:255',
        'content' => 'required|string|max:20000',
        'versionTitle' => 'required|string|max:255',
        'startsAt' => 'required|date',
    ];

    public function store()
    {
        $this->validate(array_merge($this->rules, $this->getVerficationRules()));

        Calendar::create([
            'title' => $this->title,
            'content' => $this->content,
            'is_version' => true,
            'version_title' => $this->versionTitle,
            'starts_at' => $this->startsAt,
            'writer' => $this->admin->name,
        ]);

        $this->emitSelf('versionAdded');
        $this->dispatchBrowserEvent('closeCreateModal');
        $this->resetExcept('admin');
    }

    public function edit($id)
    {
        $version = Calendar::findOrFail($id);
        $this->title = $version->title;
        $this->content = $version->content;
        $this->versionTitle = $version->version_title;
        $this->startsAt = $version->starts_at->format('Y-m-d');

        $this->dispatchBrowserEvent('openEditModal', [
            'id' => $version->id,
            'content' => $this->content,
        ]);
    }

    public function update($id)
    {
        $this->validate(array_merge($this->rules, $this->getVerficationRules()));

        $version = Calendar::findOrFail($id);

        $version->update([
            'title' => $this->title,
            'content' => $this->content,
            'version_title' => $this->versionTitle,
            'starts_at' => $this->startsAt,
        ]);

        $this->emitSelf('versionUpdated');
        $this->dispatchBrowserEvent('closeEditModal');
        $this->resetExcept('admin');
    }

    public function delete($id)
    {
        $version = Calendar::findOrFail($id);
        $version->delete();
        $this->emitSelf('versionDeleted');
    }

    public function render()
    {
        return view('livewire.versions', [
            'versions' => Calendar::version()
                ->orderByDesc('id')
                ->simplePaginate('10')
        ]);
    }
}
