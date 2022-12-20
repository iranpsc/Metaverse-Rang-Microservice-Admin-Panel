<?php

namespace App\Http\Livewire\AccessManagement;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Permissions extends Component
{
    use WithPagination;

    public $title, $name;
    public $addedRoles = [];
    protected $paginationTheme = 'bootstrap';
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
        'deletePermission' => 'delete'
    ];

    public function save()
    {
        $this->validate();
        $permission = Permission::create([
            'title' => $this->title,
            'name' => $this->name,
        ]);
        foreach ($this->addedRoles as $role) {
            $adminRole = Role::where('id', $role)->first();
            $permission->assignRole($adminRole);
        }
        session()->flash('success', 'دسترسی ایجاد شد.');
        $this->emitSelf('permissionCreated');
        $this->reset(['title', 'name']);
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function delete(Permission $permission)
    {
        $permission->delete();
        $this->emitSelf('permissionDeleted');
        session()->flash('success', 'دسترسی حذف شد.');
    }

    public function render()
    {
        return view('livewire.access-management.permissions', [
            'roles' => Role::lazy(),
            'permissions' => Permission::with('roles')->paginate(10, '*', 'permissions-listing')
        ]);
    }
}
