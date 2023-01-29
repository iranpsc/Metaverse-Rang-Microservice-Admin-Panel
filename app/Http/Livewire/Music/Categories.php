<?php

namespace App\Http\Livewire\Music;

use Livewire\Component;
use App\Models\MusicCategory;
use Livewire\WithPagination;

class Categories extends Component
{
    use WithPagination;
    public $name;
    protected $paginationTheme = 'bootstrap';
    protected $listeners = [
        'categoryCreated' => '$refresh',
        'deleteMusicCategory' => 'delete',
        'categoryDeleted' => '$refresh',
    ];

    protected $rules = [
        'name' => 'required|string'
    ];

    protected $messages = [
        'name.required' => 'نام دسته بندی را وارد کنید.'
    ];

    public function save()
    {
        $this->validate();
        MusicCategory::create(['name' => $this->name]);
        session()->flash('success', 'دسته بندی ایجاد شد.');
        $this->reset('name');
        $this->emitSelf('categoryCreated');
    }

    public function delete(MusicCategory $category)
    {
        $category->delete();
        session()->flash('success', 'دسته بندی حذف شد.');
        $this->emitSelf('categoryDeleted');
    }

    public function render()
    {
        return view('livewire.music.categories', [
            'categories' => MusicCategory::paginate(10)
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}
