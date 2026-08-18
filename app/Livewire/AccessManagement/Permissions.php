<?php

namespace App\Livewire\AccessManagement;

use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Permissions extends Component
{
    use WithPagination;

    #[Rule('required|string|min:2|max:255')]
    public $title;

    #[Rule('required|string|min:2|max:255')]
    public $name;

    public $addedRoles = [];

    public function save()
    {
        $this->validate();

        $permission = Permission::create([
            'title' => $this->title,
            'name' => $this->name,
        ]);

        $permission->assignRole($this->addedRoles);
        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
        $this->reset(['title', 'name']);
    }

    public function delete(Permission $permission)
    {
        $permission->delete();
    }

    #[Title('مدیریت دسترسی ها')]
    public function render()
    {
        return view('livewire.access-management.permissions', [
            'roles' => Role::whereNotIn('name', ['super-admin'])->get(),
            'permissions' => Permission::with('roles')->latest()->paginate(10)
        ]);
    }
}
