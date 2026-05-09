<?php

namespace App\Livewire\AccessManagement;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;

class Roles extends Component
{
    use WithPagination;

    #[Rule('required|string|min:2|max:255')]
    public $title;

    #[Rule('required|string|min:2|max:255')]
    public $name;

    public $addedPermissions = [];

    public function save()
    {
        $this->validate();

        $role = Role::create([
            'title' => $this->title,
            'name' => $this->name,
        ]);

        if(count($this->addedPermissions) > 0) {
            $role->syncPermissions($this->addedPermissions);
        }

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
        $this->reset(['title', 'name', 'addedPermissions']);
    }

    public function delete(Role $role) {
        $role->revokePermissionTo($role->permissions);
        $role->delete();
    }

    #[Title('مدیریت نقش ها')]
    public function render()
    {
        return view('livewire.access-management.roles', [
            'roles' => Role::whereNot('name', 'super-admin')->latest()->paginate(10),
            'permissions' => Permission::all(),
        ]);
    }
}
