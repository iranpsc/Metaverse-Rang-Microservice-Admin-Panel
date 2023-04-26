<?php

namespace App\Http\Livewire\AccessManagement;

use App\Traits\SendsVerificationSms;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdateAdmin extends Component
{
    use SendsVerificationSms;

    public $addedRoles = [];
    public $addedDirectPermissions = [];

    protected $listeners = [
        'removeAdminRole' => 'deleteRole',
        'removeAdminPermission' => 'deletePermission',
        'adminRoleDeleted' => '$refresh',
        'adminPermissionDeleted' => '$refresh',
    ];

    protected $rules = [
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function update()
    {
        if(count($this->addedDirectPermissions) > 0) {
            $this->admin->givePermissionTo($this->addedDirectPermissions);
        }
        if(count($this->addedRoles) > 0) {
            foreach ($this->addedRoles as $role)
            {
                $adminRole = Role::where('id',$role)->first();
                $this->admin->assignRole($adminRole);
            }
        }
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'اطلاعات با موفقیت ثبت شد']);
        $this->emitUp('adminUpdated');
    }

    public function deleteRole(Role $role) {
        $this->admin->removeRole($role);
        $this->emitSelf('adminRoleDeleted');
        $this->emitUp('adminUpdated');
    }

    public function deletePermission(Permission $permission) {
        $this->admin->revokePermissionTo($permission);
        $this->emitSelf('adminPermissionDeleted');
        $this->emitUp('adminUpdated');
    }

    public function render()
    {
        return view('livewire.access-management.update-admin', [
            'permissions' => Permission::whereNotIn('name', $this->admin->getPermissionsViaRoles()->pluck('name'))->get(),
            'roles' => Role::whereNotIn('name', $this->admin->roles->pluck('name'))->get(),
        ]);
    }
}
