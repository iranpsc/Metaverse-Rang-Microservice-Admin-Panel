<?php

namespace App\Http\Livewire\AccessManagement;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Roles extends Component
{
    use WithPagination;

    public $title, $name;
    public $addedPermissions = [];
    public $pageTitle = 'مدیریت مسئولیت ها';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'title' => 'required|string|min:2|unique:roles|max:255',
        'name' => 'required|string|min:2|unique:roles|max:255',
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

        $this->dispatchBrowserEvent('resourceModified', ['message' => 'اطلاعات با موفقیت ثبت شد']);
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
            'roles' => Role::whereNot('name', 'super-admin')
            ->with('permissions')->simplePaginate(10),
            'permissions' => Permission::lazy(),
        ]);
    }
}
