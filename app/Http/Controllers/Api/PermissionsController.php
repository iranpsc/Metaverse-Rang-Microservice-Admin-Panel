<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Morilog\Jalali\Jalalian;

class PermissionsController extends Controller
{
    /**
     * Get paginated permissions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $permissions = Permission::with('roles')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        $permissions->getCollection()->transform(function ($permission) {
            return [
                'id' => $permission->id,
                'title' => $permission->title,
                'name' => $permission->name,
                'created_at' => $permission->created_at,
                'created_at_shamsi' => Jalalian::fromCarbon($permission->created_at)->format('Y/m/d'),
                'created_at_time' => Jalalian::fromCarbon($permission->created_at)->format('H:i:s'),
                'roles' => $permission->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'title' => $role->title,
                        'name' => $role->name,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'permissions' => $permissions->items(),
                'pagination' => [
                    'current_page' => $permissions->currentPage(),
                    'last_page' => $permissions->lastPage(),
                    'per_page' => $permissions->perPage(),
                    'total' => $permissions->total(),
                    'from' => $permissions->firstItem(),
                    'to' => $permissions->lastItem(),
                ],
            ],
            'message' => 'Permissions retrieved successfully.',
        ]);
    }

    /**
     * Get all roles for permission creation/update
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
     * Get single permission with roles
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $permission = Permission::with('roles')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'permission' => [
                    'id' => $permission->id,
                    'title' => $permission->title,
                    'name' => $permission->name,
                    'roles' => $permission->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'title' => $role->title,
                            'name' => $role->name,
                        ];
                    }),
                ],
                'available_roles' => Role::whereNotIn('name', $permission->roles->pluck('name'))
                    ->whereNotIn('name', ['super-admin'])
                    ->get()
                    ->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'title' => $role->title,
                            'name' => $role->name,
                        ];
                    }),
            ],
            'message' => 'Permission retrieved successfully.',
        ]);
    }

    /**
     * Create a new permission
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:2|max:255',
            'name' => 'required|string|min:2|max:255',
            'roles' => 'nullable|array',
            'roles.*' => 'required|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $permission = Permission::create([
            'title' => $request->title,
            'name' => $request->name,
        ]);

        if ($request->has('roles') && count($request->roles) > 0) {
            $permission->assignRole($request->roles);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'permission' => [
                    'id' => $permission->id,
                    'title' => $permission->title,
                    'name' => $permission->name,
                ],
            ],
            'message' => 'Permission created successfully.',
        ]);
    }

    /**
     * Update a permission
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:2|max:255',
            'name' => 'required|string|min:2|max:255',
            'roles' => 'nullable|array',
            'roles.*' => 'required|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $permission->update([
            'title' => $request->title,
            'name' => $request->name,
        ]);

        if ($request->has('roles') && count($request->roles) > 0) {
            foreach ($request->roles as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $permission->assignRole($role);
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'permission' => [
                    'id' => $permission->id,
                    'title' => $permission->title,
                    'name' => $permission->name,
                ],
            ],
            'message' => 'Permission updated successfully.',
        ]);
    }

    /**
     * Delete a permission
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Check if permission exists
            $tableNames = config('permission.table_names');
            $permissionExists = DB::table($tableNames['permissions'])->where('id', $id)->exists();

            if (!$permissionExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission not found.',
                ], 404);
            }

            // Manually delete related records to avoid relationship access errors
            // This prevents the "Class name must be a valid object or a string" error
            // when there are NULL or invalid model_type values in model_has_permissions
            $columnNames = config('permission.column_names');
            // Get permission pivot key from config, default to 'permission_id' if null
            $permissionPivotKey = $columnNames['permission_pivot_key'] ?? 'permission_id';

            DB::transaction(function () use ($id, $tableNames, $permissionPivotKey) {
                // Delete from model_has_permissions table
                DB::table($tableNames['model_has_permissions'])
                    ->where($permissionPivotKey, $id)
                    ->delete();

                // Delete from role_has_permissions table
                DB::table($tableNames['role_has_permissions'])
                    ->where($permissionPivotKey, $id)
                    ->delete();

                // Delete the permission directly using DB to bypass Eloquent events
                // This avoids triggering the 'deleting' event which tries to access relationships
                DB::table($tableNames['permissions'])
                    ->where('id', $id)
                    ->delete();
            });

            // Clear the permission cache after deletion
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            return response()->json([
                'success' => true,
                'message' => 'Permission deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Permission deletion failed', [
                'permission_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete permission: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove role from permission
     *
     * @param int $permissionId
     * @param int $roleId
     * @return JsonResponse
     */
    public function removeRole(int $permissionId, int $roleId): JsonResponse
    {
        $permission = Permission::findOrFail($permissionId);
        $role = Role::findOrFail($roleId);

        $permission->removeRole($role);

        return response()->json([
            'success' => true,
            'message' => 'Role removed from permission successfully.',
        ]);
    }
}

