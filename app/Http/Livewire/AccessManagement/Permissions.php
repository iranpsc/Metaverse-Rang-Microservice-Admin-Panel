<?php

namespace App\Http\Livewire\AccessManagement;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Permissions extends Component
{
    public $title, $name;
    public $addedRoles = [];

    protected $rules = [
        'title' => 'required|string',
        'name' => 'required|string|min:2',
    ];

    protected $messages = [
        'title.required' => 'عنوان دسترسی را وارد کنید',
        'name.required' => 'نام دسترسی را وارد کنید'
    ];

    protected $listeners = [
        'permissionCreated' => '$refresh',
        'permissionUpdated' => '$refresh',
        'permissionDeleted' => '$refresh',
        'deletePermission'  => 'delete'
    ];

    public function save()
    {
        $this->validate();
        $permission = Permission::create([
            'title' => $this->title,
            'name' => $this->name,
        ]);
        $permission->assignRole($this->addedRoles);
        session()->flash('success', 'دسترسی ایجاد شد.');
        $this->emitSelf('permissionCreated');
        $this->reset(['title', 'name']);
    }

    public function updated($prop) {
        $this->validateOnly($prop);
    }

    public function delete(Permission $permission) {
        $permission->delete();
        $this->emitSelf('permissionDeleted');
        session()->flash('success', 'دسترسی حذف شد.');
    }
    public function render()
    {
        return view('livewire.access-management.permissions', [
            'roles' => Role::whereNotIn('name', ['super-admin'])->lazy(),
            'permissions' => Permission::with('roles')->paginate(10, '*', 'permissions-listing')
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}
