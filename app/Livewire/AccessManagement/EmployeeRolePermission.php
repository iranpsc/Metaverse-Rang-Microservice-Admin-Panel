<?php

namespace App\Livewire\AccessManagement;

use App\Models\Admin;
use App\Models\Employee\Employee;
use App\Notifications\AccountCreatedNotification;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;

class EmployeeRolePermission extends Component
{
    use SendsVerificationSms;

    public $employee, $roles = [];

    public function rules()
    {
        return [
            'employee' => 'required|exists:employees,id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|integer|exists:roles,id',
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_verify_code'
            ],
            'access_password' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_access_password'
            ]
        ];
    }

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function save()
    {
        $this->validate();

        $employee = Employee::findOrFail($this->employee);

        $password = Str::random(8);

        $access_password = random_int(100000, 999999);

        $admin = Admin::create([
            'name' => $employee->fname . ' ' . $employee->lname,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'password' => Hash::make($password),
            'access_password' => Hash::make($access_password),
        ]);

        $admin->assignRole($this->roles);

        $admin->notify(new AccountCreatedNotification($employee->email, $password, $access_password));

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
        $this->reset(['employee', 'roles']);
    }

    public function delete(Admin $admin)
    {
        if ($admin->roles) {
            foreach ($admin->roles as $role) {
                $admin->removeRole($role);
            }
        }

        if ($admin->getDirectPermissions()) {
            $admin->revokePermissionTo($admin->getDirectPermissions());
        }

        $admin->delete();
    }

    #[Title('مدیریت دسترسی کارمندان')]
    public function render()
    {
        return view('livewire.access-management.employee-role-permission', [
            'admins' => Admin::whereNotIn('id', [Auth::id()])->with(['roles', 'permissions'])->get(),
            'employees' => Employee::select(['id', 'fname', 'lname'])->get(),
            'defined_roles' => Role::whereNotIn('name', ['super-admin'])->get(),
        ]);
    }
}
