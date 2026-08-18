<?php

namespace App\Livewire\AccessManagement;

use Livewire\Component;
use Spatie\Permission\Models\Permission;

class UpdateRole extends Component
{
    public $title, $name, $role;
    public $addedPermissions = [];

    protected $rules = [
        'title' => 'required|string',
        'name' => 'required|string|min:2',
    ];

    public function mount($role) {
        $this->role = $role;
        $this->title = $role->title;
        $this->name = $role->name;
    }

    public function save()
    {
        $this->validate();

        $this->role->update([
            'title' => $this->title,
            'name' => $this->name,
        ]);

        if(count($this->addedPermissions) > 0) {
            $this->role->givePermissionTo($this->addedPermissions);
        }

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }

    public function removePermission(Permission $permission)
    {
        $this->role->revokePermissionTo($permission);
        $this->dispatch('rolePermissionRemoved')->self();
    }

    public function render()
    {
        return view('livewire.access-management.update-role', [
            'permissions' => Permission::whereNotIn('name', $this->role->permissions->pluck('name'))->get()
        ]);
    }
}
