<?php

namespace App\Http\Livewire\AccessManagement;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Roles extends Component
{
    public $title, $name;
    public $addedPermissions = [];

    protected $rules = [
        'title' => 'required|string',
        'name' => 'required|string|min:2',
    ];

    protected $messages = [
        'title.required' => 'عنوان مسئولیت را وارد کنید',
        'name.required' => 'نام مسئولیت را وارد کنید'
    ];

    protected $listeners = [
        'roleCreated' => '$refresh',
        'roleUpdated' => '$refresh',
        'roleDeleted' => '$refresh',
        'deleteRole'  => 'delete'
    ];

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
        session()->flash('success', 'مسئولیت ایجاد شد.');
        $this->emitSelf('roleCreated');
        $this->reset(['title', 'name', 'addedPermissions']);
    }

    public function updated($prop) {
        $this->validateOnly($prop);
    }

    public function delete(Role $role) {
        $role->revokePermissionTo($role->permissions);
        $role->delete();
        $this->emitSelf('roleDeleted');
        session()->flash('success', 'مسئولیت حذف شد.');
    }

    public function render()
    {
        return view('livewire.access-management.roles', [
            'roles' => Role::whereNotIn('name', ['Super Admin'])
            ->with('permissions')->paginate(10, '*', 'roles-listing'),
            'permissions' => Permission::lazy(),
        ]);
    }
}
