<?php

namespace App\Http\Livewire\AccessManagement;

use App\Models\Admin;
use App\Models\Employee\Employee;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EmployeeRolePermission extends Component
{
    public $password, $accessPassword, $employee;
    public $addedRoles = [];
    public $addedPermissions = [];

    protected $rules = [
        'employee' => 'required',
        'password' => 'required',
        'accessPassword' => 'required'
    ];

    protected $messages = [
        'employee.required' => 'کارمند را انتخاب کنید',
        'password.required' => 'رمز عبور را وارد کنید',
        'accessPassword.required' => 'رمز دسترسی را وارد کنید'
    ];

    protected $listeners = [
        'adminCreated' => '$refresh',
        'adminUpdated' => '$refresh',
        'adminDeleted' => '$refresh',
        'deleteAdmin'  => 'delete'
    ];

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function save()
    {
        $this->validate();
        if(empty($this->addedRoles) && empty($this->addedPermissions)) {
            $this->addError('noRolesOrPermissionsSelected', 'حداقل یک مسئولیت یا دسترسی به این کارمند اختصاص دهید!');
        } else {
            $employee = Employee::select(['fname', 'lname', 'email', 'phone'])->where('id', $this->employee)->first();
            $admin = Admin::create([
                'name' => implode(' ', [$employee->fname, $employee->lname]),
                'email' => $employee->email,
                'phone' => $employee->phone,
                'password' => Hash::make($this->password),
                'access_password' => Hash::make($this->accessPassword),
                'active' => 1
            ]);
            if (count($this->addedRoles) > 0) {
                $admin->assignRole($this->addedRoles);
            }
            if (count($this->addedPermissions)) {
                $admin->givePermissionTo($this->addedPermissions);
            }
            session()->flash('success', 'کارمند تعریف شد!');
            $this->reset(['password', 'employee', 'accessPassword', 'addedRoles', 'addedPermissions']);
            $this->emitSelf('adminCreated');
        }
    }

    public function delete(Admin $admin) {
        if($admin->roles) {
            $admin->removeRole($admin->roles);
        }
        if($admin->getDirectPermissions()) {
            $admin->revokePermissionTo($admin->getDirectPermissions());
        }
        $admin->delete();
        session()->flash('success', 'کارمند حذف شد!');
        $this->emitSelf('adminDeleted');
    }
    public function render()
    {
        return view('livewire.access-management.employee-role-permission', [
            'admins'      => Admin::with(['roles', 'permissions'])->lazy(),
            'employees'   => Employee::select(['id', 'fname', 'lname'])->get(),
            'roles'       => Role::whereNotIn('name', ['Super Admin'])->get(),
            'permissions' => Permission::lazy(),
        ]);
    }
}
