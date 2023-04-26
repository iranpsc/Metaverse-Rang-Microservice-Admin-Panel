<?php

namespace App\Http\Livewire\AccessManagement;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class UpdatePermission extends Component
{
    public $title, $name, $permission;
    public $addedRoles = [];

    protected $rules = [
        'title' => 'required|string',
        'name' => 'required|string|min:2',
    ];

    protected $listeners = [
        'removePermissionRole' => 'removeRole',
        'permissionRoleRemoved' => '$refresh'
    ];

    public function mount($permission)
    {
        $this->title = $permission->title;
        $this->name = $permission->name;
    }

    public function save()
    {
        $this->validate();
        $this->permission->update([
            'title' => $this->title,
            'name' => $this->name,
        ]);
        if (count($this->addedRoles) > 0) {
            foreach ($this->addedRoles as $role)
            {
                $userRole = Role::where('id',$role)->first();
            $this->permission->assignRole($userRole);
            }
        }
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'اطلاعات با موفقیت ثبت شد']);
        $this->emitUp('permissionUpdated');
    }

    public function removeRole(Role $role)
    {
        $this->permission->removeRole($role);
        $this->emitSelf('permissionRoleRemoved');
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.access-management.update-permission', [
            'roles' => Role::whereNotIn('name', $this->permission->roles->pluck('name'))->get(),
        ]);
    }
}
