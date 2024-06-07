<?php

namespace App\Livewire\AccessManagement;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UpdateAdmin extends Component
{
    public $addedRoles = [], $admin;
    public $addedDirectPermissions = [];

    public function rules()
    {
        return [
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_verify_code'
            ],
            'access_password' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_access_password'
            ],
        ];
    }

    public function save()
    {
        if (count($this->addedDirectPermissions) > 0) {
            $this->admin->givePermissionTo($this->addedDirectPermissions);
        }
        if (count($this->addedRoles) > 0) {
            foreach ($this->addedRoles as $role) {
                $adminRole = Role::where('id', $role)->first();
                $this->admin->assignRole($adminRole);
            }
        }
        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }

    public function deleteRole(Role $role)
    {
        $this->admin->removeRole($role);
    }

    public function deletePermission(Permission $permission)
    {
        $this->admin->revokePermissionTo($permission);
    }

    public function render()
    {
        return view('livewire.access-management.update-admin', [
            'permissions' => Permission::whereNotIn('name', $this->admin->getPermissionsViaRoles()->pluck('name'))->get(),
            'roles' => Role::whereNotIn('name', $this->admin->roles->pluck('name'))->get(),
        ]);
    }
}
