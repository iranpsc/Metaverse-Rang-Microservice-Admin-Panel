<?php

namespace App\Http\Livewire\AccessManagement;

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

    protected $listeners = [
        'removeRolePermission' => 'removePermission',
        'rolePermissionRemoved' => '$refresh'
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
        session()->flash('success', 'مسئولیت بروزرسانی شد.');
        $this->emitUp('roleUpdated');
    }

    public function updated($prop) {
        $this->validateOnly($prop);
    }

    public function removePermission(Permission $permission)
    {
        $this->role->revokePermissionTo($permission);
        $this->emitSelf('rolePermissionRemoved');
    }

    public function render()
    {
        return view('livewire.access-management.update-role', [
            'permissions' => Permission::whereNotIn('name', $this->role->permissions->pluck('name'))->get()
        ]);
    }
}
