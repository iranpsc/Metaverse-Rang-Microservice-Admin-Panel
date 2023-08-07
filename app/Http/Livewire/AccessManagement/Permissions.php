<?php

namespace App\Http\Livewire\AccessManagement;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Permissions extends Component
{
    use WithPagination;

    public $title, $name;
    public $addedRoles = [];
    public $pageTitle = 'مدیریت دسترسی ها';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'title' => 'required|string',
        'name' => 'required|string|min:2',
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
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'اطلاعات با موفقیت ثبت شد']);
        $this->emitSelf('permissionCreated');
        $this->reset(['title', 'name']);
    }

    public function updated($prop) {
        $this->validateOnly($prop);
    }

    public function delete(Permission $permission) {
        $permission->delete();
        $this->emitSelf('permissionDeleted');
    }
    public function render()
    {
        return view('livewire.access-management.permissions', [
            'roles' => Role::whereNotIn('name', ['super-admin'])->lazy(),
            'permissions' => Permission::with('roles')->paginate(10, '*', 'permissions-listing')
        ]);
    }
}
