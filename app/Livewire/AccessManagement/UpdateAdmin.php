<?php

namespace App\Livewire\AccessManagement;

use App\Traits\SendsVerificationSms;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UpdateAdmin extends Component
{
    use SendsVerificationSms;

    public $addedRoles = [], $adminUser;
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
            $this->adminUser->givePermissionTo($this->addedDirectPermissions);
        }
        if (count($this->addedRoles) > 0) {
            foreach ($this->addedRoles as $role) {
                $adminRole = Role::where('id', $role)->first();
                $this->adminUser->assignRole($adminRole);
            }
        }
        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }

    public function deleteRole(Role $role)
    {
        $this->adminUser->removeRole($role);
    }

    public function deletePermission(Permission $permission)
    {
        $this->adminUser->revokePermissionTo($permission);
    }

    public function render()
    {
        return view('livewire.access-management.update-admin', [
            'permissions' => Permission::whereNotIn('name', $this->adminUser->getPermissionsViaRoles()->pluck('name'))->get(),
            'roles' => Role::whereNotIn('name', $this->adminUser->roles->pluck('name'))->get(),
        ]);
    }
}
