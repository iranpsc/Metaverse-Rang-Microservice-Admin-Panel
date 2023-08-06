<?php

namespace App\Http\Livewire\Versions;

use App\Models\Calendar;
use App\Traits\SendsVerificationSms;
use Livewire\Component;
use Livewire\WithPagination;

class Listing extends Component
{
    use WithPagination, SendsVerificationSms;

    public $title, $description, $versionTitle, $startsAt, $btnLink, $btnName;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'versionAdded' => '$refresh',
        'versionUpdated' => '$refresh',
        'versionDeleted' => '$refresh',
    ];

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string|max:20000',
        'versionTitle' => 'required|string|max:255',
        'startsAt' => 'required|date',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = auth()->guard('admin')->user();
    }

    public function store()
    {
        $this->validate();

        Calendar::create([
            'title' => $this->title,
            'content' => $this->description,
            'is_version' => true,
            'version_title' => $this->versionTitle,
            'starts_at' => $this->startsAt,
            'writer' => $this->admin->name,
        ]);

        $this->emitSelf('versionAdded');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'نسخه جدید با موفقیت افزوده شد.']);
        $this->resetExcept('admin');
    }

    public function edit(Calendar $version)
    {
        $this->title = $version->title;
        $this->description = $version->content;
        $this->versionTitle = $version->version_title;
        $this->startsAt = $version->starts_at->format('Y-m-d');

        $this->dispatchBrowserEvent('openEditModal', [
            'id' => $version->id,
            'description' => $this->description,
        ]);
    }

    public function update(Calendar $version)
    {
        $this->validate();

        $version->update([
            'title' => $this->title,
            'content' => $this->description,
            'version_title' => $this->versionTitle,
            'starts_at' => $this->startsAt,
            'writer' => $this->admin->name,
        ]);

        $this->emitSelf('versionUpdated');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'نسخه با موفقیت ویرایش شد.']);
        $this->reset(['phone_verification', 'access_password']);
    }

    public function delete(Calendar $version)
    {
        $version->delete();
        $this->emitSelf('versionDeleted');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'نسخه با موفقیت حذف شد.']);
    }

    public function resetModal()
    {
        $this->resetExcept('admin');
    }

    public function render()
    {
        return view('livewire.versions.listing', [
            'versions' => Calendar::version()->orderByDesc('version_title')->simplePaginate('10')
        ])->extends('layouts.app')->section('content');
    }
}
