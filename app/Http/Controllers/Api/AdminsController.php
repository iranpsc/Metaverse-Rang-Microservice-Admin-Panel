<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Employee\Employee;
use App\Notifications\AccountCreatedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Morilog\Jalali\Jalalian;

class AdminsController extends Controller
{
    /**
     * Get all admins
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $currentAdminId = Auth::guard('admin')->id();

        $admins = Admin::whereNotIn('id', [$currentAdminId])
            ->with(['roles', 'permissions'])
            ->get()
            ->filter(function ($admin) {
                // Filter out super-admin
                return !$admin->hasRole('super-admin');
            })
            ->map(function ($admin) {
                return [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'phone' => $admin->phone,
                    'created_at' => $admin->created_at,
                    'created_at_shamsi' => Jalalian::fromCarbon($admin->created_at)->format('Y/m/d'),
                    'created_at_time' => Jalalian::fromCarbon($admin->created_at)->format('H:i:s'),
                    'roles' => $admin->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'title' => $role->title,
                            'name' => $role->name,
                        ];
                    }),
                    'permissions' => $admin->getDirectPermissions()->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'title' => $permission->title,
                            'name' => $permission->name,
                        ];
                    }),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'admins' => $admins,
            ],
            'message' => 'Admins retrieved successfully.',
        ]);
    }

    /**
     * Get all employees
     *
     * @return JsonResponse
     */
    public function getEmployees(): JsonResponse
    {
        $employees = Employee::select(['id', 'fname', 'lname'])->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->fname . ' ' . $employee->lname,
                'fname' => $employee->fname,
                'lname' => $employee->lname,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'employees' => $employees,
            ],
            'message' => 'Employees retrieved successfully.',
        ]);
    }

    /**
     * Get all roles for admin assignment
     *
     * @return JsonResponse
     */
    public function getRoles(): JsonResponse
    {
        $roles = Role::whereNotIn('name', ['super-admin'])->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'title' => $role->title,
                'name' => $role->name,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $roles,
            ],
            'message' => 'Roles retrieved successfully.',
        ]);
    }

    /**
     * Get single admin with roles and permissions
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $admin = Admin::with(['roles', 'permissions'])->findOrFail($id);

        if ($admin->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot access super-admin',
            ], 403);
        }

        // Get permissions not already assigned via roles (filter by web or admin guard)
        $permissionsViaRoles = $admin->getPermissionsViaRoles()->pluck('name');
        $availablePermissions = Permission::whereNotIn('name', $permissionsViaRoles)
            ->whereIn('guard_name', ['web', 'admin'])
            ->get()
            ->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'title' => $permission->title,
                    'name' => $permission->name,
                ];
            });

        // Get roles not already assigned (filter by web or admin guard to match Admin model's guard)
        $assignedRoleNames = $admin->roles->pluck('name')->toArray();

        $availableRoles = Role::whereNotIn('name', $assignedRoleNames)
            ->whereNotIn('name', ['super-admin'])
            ->whereIn('guard_name', ['web', 'admin'])
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'title' => $role->title,
                    'name' => $role->name,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'phone' => $admin->phone,
                    'roles' => $admin->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'title' => $role->title,
                            'name' => $role->name,
                        ];
                    }),
                    'permissions' => $admin->getDirectPermissions()->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'title' => $permission->title,
                            'name' => $permission->name,
                        ];
                    }),
                ],
                'available_roles' => $availableRoles,
                'available_permissions' => $availablePermissions,
            ],
            'message' => 'Admin retrieved successfully.',
        ]);
    }

    /**
     * Create a new admin from employee
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
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
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee = Employee::findOrFail($request->employee);

        // Check if admin already exists for this employee
        $existingAdmin = Admin::where('email', $employee->email)->first();
        if ($existingAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin already exists for this employee',
            ], 422);
        }

        $password = Str::random(8);
        $access_password = random_int(100000, 999999);

        $admin = Admin::create([
            'name' => $employee->fname . ' ' . $employee->lname,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'password' => Hash::make($password),
            'access_password' => Hash::make($access_password),
        ]);

        // Find roles with web or admin guard (matching the Admin model's guard)
        $roles = Role::whereIn('id', $request->roles)
            ->whereIn('guard_name', ['web', 'admin'])
            ->get();

        if ($roles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid roles found for the specified guard',
            ], 422);
        }

        $admin->assignRole($roles);

        $admin->notify(new AccountCreatedNotification($employee->email, $password, $access_password));

        return response()->json([
            'success' => true,
            'data' => [
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                ],
            ],
            'message' => 'Admin created successfully.',
        ]);
    }

    /**
     * Update admin roles and permissions
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $admin = Admin::findOrFail($id);

        if ($admin->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update super-admin',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'roles' => 'nullable|array',
            'roles.*' => 'required|integer|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'required|string|exists:permissions,name',
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->has('roles') && count($request->roles) > 0) {
            // Find roles with web or admin guard (matching the Admin model's guard)
            $roles = Role::whereIn('id', $request->roles)
                ->whereIn('guard_name', ['web', 'admin'])
                ->get();

            if ($roles->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid roles found for the specified guard',
                ], 422);
            }

            foreach ($roles as $role) {
                $admin->assignRole($role);
            }
        }

        if ($request->has('permissions') && count($request->permissions) > 0) {
            $admin->givePermissionTo($request->permissions);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                ],
            ],
            'message' => 'Admin updated successfully.',
        ]);
    }

    /**
     * Delete an admin
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $admin = Admin::findOrFail($id);

        if ($admin->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete super-admin',
            ], 403);
        }

        // Remove all roles
        if ($admin->roles) {
            foreach ($admin->roles as $role) {
                $admin->removeRole($role);
            }
        }

        // Remove all direct permissions
        if ($admin->getDirectPermissions()) {
            $admin->revokePermissionTo($admin->getDirectPermissions());
        }

        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully.',
        ]);
    }

    /**
     * Remove role from admin
     *
     * @param int $adminId
     * @param int $roleId
     * @return JsonResponse
     */
    public function removeRole(int $adminId, int $roleId): JsonResponse
    {
        $admin = Admin::findOrFail($adminId);
        $role = Role::findOrFail($roleId);

        $admin->removeRole($role);

        return response()->json([
            'success' => true,
            'message' => 'Role removed from admin successfully.',
        ]);
    }

    /**
     * Remove permission from admin
     *
     * @param int $adminId
     * @param int $permissionId
     * @return JsonResponse
     */
    public function removePermission(int $adminId, int $permissionId): JsonResponse
    {
        $admin = Admin::findOrFail($adminId);
        $permission = Permission::findOrFail($permissionId);

        $admin->revokePermissionTo($permission);

        return response()->json([
            'success' => true,
            'message' => 'Permission removed from admin successfully.',
        ]);
    }
}

