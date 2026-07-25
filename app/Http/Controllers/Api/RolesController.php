<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Morilog\Jalali\Jalalian;

class RolesController extends Controller
{
    /**
     * Get paginated roles
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $roles = Role::whereNot('name', 'super-admin')
            ->with('permissions')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        $roles->getCollection()->transform(function ($role) {
            return [
                'id' => $role->id,
                'title' => $role->title,
                'name' => $role->name,
                'created_at' => $role->created_at,
                'created_at_shamsi' => Jalalian::fromCarbon($role->created_at)->format('Y/m/d'),
                'created_at_time' => Jalalian::fromCarbon($role->created_at)->format('H:i:s'),
                'permissions' => $role->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'title' => $permission->title,
                        'name' => $permission->name,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $roles->items(),
                'pagination' => [
                    'current_page' => $roles->currentPage(),
                    'last_page' => $roles->lastPage(),
                    'per_page' => $roles->perPage(),
                    'total' => $roles->total(),
                    'from' => $roles->firstItem(),
                    'to' => $roles->lastItem(),
                ],
            ],
            'message' => 'Roles retrieved successfully.',
        ]);
    }

    /**
     * Get all permissions for role creation/update
     *
     * @return JsonResponse
     */
    public function getPermissions(): JsonResponse
    {
        $permissions = Permission::all()->map(function ($permission) {
            return [
                'id' => $permission->id,
                'title' => $permission->title,
                'name' => $permission->name,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'permissions' => $permissions,
            ],
            'message' => 'Permissions retrieved successfully.',
        ]);
    }

    /**
     * Get single role with permissions
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $role = Role::with('permissions')->findOrFail($id);

        if ($role->name === 'super-admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot access super-admin role',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'role' => [
                    'id' => $role->id,
                    'title' => $role->title,
                    'name' => $role->name,
                    'permissions' => $role->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'title' => $permission->title,
                            'name' => $permission->name,
                        ];
                    }),
                ],
                'available_permissions' => Permission::whereNotIn('name', $role->permissions->pluck('name'))
                    ->get()
                    ->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'title' => $permission->title,
                            'name' => $permission->name,
                        ];
                    }),
            ],
            'message' => 'Role retrieved successfully.',
        ]);
    }

    /**
     * Create a new role
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:2|max:255',
            'name' => 'required|string|min:2|max:255|unique:roles,name',
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

        $role = Role::create([
            'title' => $request->title,
            'name' => $request->name,
        ]);

        if ($request->has('permissions') && count($request->permissions) > 0) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'role' => [
                    'id' => $role->id,
                    'title' => $role->title,
                    'name' => $role->name,
                ],
            ],
            'message' => 'Role created successfully.',
        ]);
    }

    /**
     * Update a role
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'super-admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update super-admin role',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:2|max:255',
            'name' => 'required|string|min:2|max:255|unique:roles,name,' . $id,
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

        $role->update([
            'title' => $request->title,
            'name' => $request->name,
        ]);

        // Sync permissions - replace existing permissions with new ones
        // If permissions array is provided (even if empty), sync with it
        // If not provided at all, keep existing permissions unchanged
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }
        // If permissions key is not present, don't change permissions

        return response()->json([
            'success' => true,
            'data' => [
                'role' => [
                    'id' => $role->id,
                    'title' => $role->title,
                    'name' => $role->name,
                ],
            ],
            'message' => 'Role updated successfully.',
        ]);
    }

    /**
     * Delete a role
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'super-admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete super-admin role',
            ], 403);
        }

        // Manually delete pivot table records to avoid relationship issues
        DB::table('role_has_permissions')->where('role_id', $role->id)->delete();
        DB::table('model_has_roles')->where('role_id', $role->id)->delete();

        // Delete the role directly from database to avoid model events
        DB::table('roles')->where('id', $role->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.',
        ]);
    }

    /**
     * Remove permission from role
     *
     * @param int $roleId
     * @param int $permissionId
     * @return JsonResponse
     */
    public function removePermission(int $roleId, int $permissionId): JsonResponse
    {
        $role = Role::findOrFail($roleId);
        $permission = Permission::findOrFail($permissionId);

        $role->revokePermissionTo($permission);

        return response()->json([
            'success' => true,
            'message' => 'Permission removed from role successfully.',
        ]);
    }
}

