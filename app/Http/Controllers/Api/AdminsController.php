<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Notifications\AccountCreatedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

class AdminsController extends Controller
{
    /**
     * Get all admins
     */
    public function index(): JsonResponse
    {
        $currentAdminId = Auth::guard('admin')->id();

        $admins = Admin::whereNotIn('id', [$currentAdminId])
            ->with(['roles', 'permissions'])
            ->get()
            ->filter(function ($admin) {
                // Filter out super-admin
                return ! $admin->hasRole('super-admin');
            });

        $userCodesByEmail = User::query()
            ->whereIn('email', $admins->pluck('email')->filter()->unique())
            ->pluck('code', 'email');

        $admins = $admins->map(function ($admin) use ($userCodesByEmail) {
            return [
                'id' => $admin->id,
                'code' => $userCodesByEmail->get($admin->email),
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
     * Search users for Select2 dropdown when creating an admin.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $search = trim($validated['search'] ?? '');
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 5);

        $existingAdminEmails = Admin::query()->select('email');

        $query = User::query()
            ->select(['id', 'name', 'code', 'email', 'phone'])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            });
        } else {
            $query->whereNotIn('email', $existingAdminEmails)
                ->whereNotNull('phone')
                ->where('phone', '!=', '');
        }

        $users = $query->paginate($perPage, ['*'], 'page', $page);
        $adminEmails = Admin::query()->pluck('email')->flip();

        $options = collect($users->items())->map(function (User $user) use ($adminEmails) {
            $code = $user->code ? " ({$user->code})" : '';
            $alreadyAdmin = $adminEmails->has($user->email);
            $missingPhone = blank($user->phone);
            $suffix = $alreadyAdmin ? ' — مدیر است' : ($missingPhone ? ' — بدون شماره تماس' : '');

            return [
                'value' => $user->id,
                'label' => $user->name.$code.$suffix,
                'disabled' => $alreadyAdmin || $missingPhone,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'options' => $options,
                'pagination' => [
                    'more' => $users->hasMorePages(),
                ],
            ],
            'message' => 'Users retrieved successfully.',
        ]);
    }

    /**
     * Get all roles for admin assignment
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
     * Create a new admin from a user
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|integer|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::findOrFail($request->user_id);

        if (blank($user->email)) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have an email address',
            ], 422);
        }

        if (blank($user->phone)) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have a phone number',
            ], 422);
        }

        $existingAdmin = Admin::where('email', $user->email)->first();
        if ($existingAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin already exists for this user',
            ], 422);
        }

        $password = Str::random(8);
        $access_password = random_int(100000, 999999);

        $admin = Admin::create([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'password' => Hash::make($password),
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

        try {
            $admin->notify(new AccountCreatedNotification($user->email, $password, $access_password));
        } catch (Throwable $exception) {
            report($exception);
        }

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
