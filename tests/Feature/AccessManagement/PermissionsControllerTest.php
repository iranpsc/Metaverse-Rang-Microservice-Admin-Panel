<?php

namespace Tests\Feature\AccessManagement;

use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesAccessManagementApiSchema;
use Tests\TestCase;

class PermissionsControllerTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesAccessManagementApiSchema;

    private const INDEX_PATH = '/api/permissions';

    private const ROLES_PATH = '/api/permissions/roles';

    private const INDEX_SUCCESS_MESSAGE = 'Permissions retrieved successfully.';

    private const ROLES_SUCCESS_MESSAGE = 'Roles retrieved successfully.';

    private const SHOW_SUCCESS_MESSAGE = 'Permission retrieved successfully.';

    private const STORE_SUCCESS_MESSAGE = 'Permission created successfully.';

    private const UPDATE_SUCCESS_MESSAGE = 'Permission updated successfully.';

    private const DESTROY_SUCCESS_MESSAGE = 'Permission deleted successfully.';

    private const REMOVE_ROLE_SUCCESS_MESSAGE = 'Role removed from permission successfully.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAccessManagementApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_store_returns_unauthorized(): void
    {
        $this->postJson(self::INDEX_PATH, $this->validStorePayload())->assertUnauthorized();
    }

    public function test_unauthenticated_show_returns_unauthorized(): void
    {
        $permission = $this->createPermission();

        $this->getJson($this->permissionPath($permission))->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $permission = $this->createPermission();

        $this->putJson($this->permissionPath($permission), $this->validUpdatePayload($permission))
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $permission = $this->createPermission();

        $this->deleteJson($this->permissionPath($permission))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)->assertOk()->assertJsonPath('success', true);
        $this->getJson(self::ROLES_PATH)->assertOk()->assertJsonPath('success', true);

        $permission = $this->createPermission(['name' => 'view-dashboard-'.Str::uuid()]);

        $this->getJson($this->permissionPath($permission))->assertOk()->assertJsonPath('success', true);

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'create-dashboard-'.Str::uuid(),
        ]))->assertOk()->assertJsonPath('success', true);

        $this->putJson($this->permissionPath($permission), $this->validUpdatePayload($permission, [
            'title' => 'Updated Permission',
        ]))->assertOk()->assertJsonPath('success', true);

        $toDelete = $this->createPermission(['name' => 'delete-me-'.Str::uuid()]);

        $this->deleteJson($this->permissionPath($toDelete))
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_authenticated_regular_admin_can_index_permissions(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_paginated_permissions_with_roles(): void
    {
        $this->actingAsSuperAdmin();

        $role = $this->createRole(['name' => 'perm-role-'.Str::uuid(), 'title' => 'Perm Role']);
        $permission = $this->createPermission([
            'name' => 'manage-settings-'.Str::uuid(),
            'title' => 'Manage Settings',
        ]);
        $permission->assignRole($role);

        $response = $this->getJson(self::INDEX_PATH.'?per_page=5&page=1');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'data' => [
                    'permissions' => [
                        '*' => [
                            'id',
                            'title',
                            'name',
                            'created_at',
                            'created_at_shamsi',
                            'created_at_time',
                            'roles',
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'from',
                        'to',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'id' => $permission->id,
                'name' => $permission->name,
            ]);
    }

    // -------------------------------------------------------------------------
    // getRoles
    // -------------------------------------------------------------------------

    public function test_get_roles_excludes_super_admin(): void
    {
        $this->actingAsSuperAdmin();

        $role = $this->createRole(['name' => 'assignable-'.Str::uuid(), 'title' => 'Assignable']);
        Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'admin'],
            ['title' => 'Super Admin']
        );

        $response = $this->getJson(self::ROLES_PATH);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::ROLES_SUCCESS_MESSAGE)
            ->assertJsonFragment([
                'id' => $role->id,
                'name' => $role->name,
            ]);

        $roleNames = collect($response->json('data.roles'))->pluck('name');
        $this->assertFalse($roleNames->contains('super-admin'));
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_returns_permission_with_roles_and_available_roles(): void
    {
        $this->actingAsSuperAdmin();

        $assignedRole = $this->createRole(['name' => 'assigned-role-'.Str::uuid(), 'title' => 'Assigned']);
        $availableRole = $this->createRole(['name' => 'available-role-'.Str::uuid(), 'title' => 'Available']);
        $permission = $this->createPermission([
            'name' => 'edit-content-'.Str::uuid(),
            'title' => 'Edit Content',
        ]);
        $permission->assignRole($assignedRole);

        $response = $this->getJson($this->permissionPath($permission));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonPath('data.permission.id', $permission->id)
            ->assertJsonFragment(['name' => $assignedRole->name])
            ->assertJsonFragment(['name' => $availableRole->name]);

        $availableRoleIds = collect($response->json('data.available_roles'))->pluck('id');

        $this->assertTrue($availableRoleIds->contains($availableRole->id));
        $this->assertFalse($availableRoleIds->contains($assignedRole->id));
    }

    public function test_show_nonexistent_permission_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson('/api/permissions/99999')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_permission_with_roles(): void
    {
        $this->actingAsSuperAdmin();

        $role = $this->createRole([
            'name' => 'store-role-'.Str::uuid(),
            'title' => 'Store Role',
        ]);
        $name = 'publish-content-'.Str::uuid();

        $response = $this->postJson(self::INDEX_PATH, [
            'title' => 'Publish Content',
            'name' => $name,
            'roles' => [$role->name],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.permission.name', $name)
            ->assertJsonPath('data.permission.title', 'Publish Content');

        $permission = Permission::where('name', $name)->first();
        $this->assertTrue($permission->roles->contains('id', $role->id));
    }

    public function test_store_creates_permission_without_roles(): void
    {
        $this->actingAsSuperAdmin();

        $name = 'standalone-perm-'.Str::uuid();

        $this->postJson(self::INDEX_PATH, [
            'title' => 'Standalone Permission',
            'name' => $name,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $permission = Permission::where('name', $name)->first();
        $this->assertCount(0, $permission->roles);
    }

    public function test_store_validation_fails_for_missing_required_fields(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJson(self::INDEX_PATH, []);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['title', 'name']);
    }

    public function test_store_validation_fails_for_invalid_role_name(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJson(self::INDEX_PATH, [
            'title' => 'Invalid Role Permission',
            'name' => 'invalid-role-perm-'.Str::uuid(),
            'roles' => ['missing-role'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['roles.0']);
    }

    public function test_store_validation_fails_for_short_title_and_name(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJson(self::INDEX_PATH, [
            'title' => 'X',
            'name' => 'Y',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'name']);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_modifies_permission_and_assigns_additional_roles(): void
    {
        $this->actingAsSuperAdmin();

        $existingRole = $this->createRole(['name' => 'existing-role-'.Str::uuid(), 'title' => 'Existing']);
        $newRole = $this->createRole(['name' => 'new-role-'.Str::uuid(), 'title' => 'New']);
        $permission = $this->createPermission([
            'name' => 'update-target-'.Str::uuid(),
            'title' => 'Update Target',
        ]);
        $permission->assignRole($existingRole);

        $updatedName = 'update-target-renamed-'.Str::uuid();

        $response = $this->putJson($this->permissionPath($permission), [
            'title' => 'Updated Permission Title',
            'name' => $updatedName,
            'roles' => [$newRole->name],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.permission.name', $updatedName);

        $permission->refresh();
        $this->assertSame('Updated Permission Title', $permission->title);
        $this->assertTrue($permission->roles->contains('id', $existingRole->id));
        $this->assertTrue($permission->roles->contains('id', $newRole->id));
    }

    public function test_update_validation_fails_for_missing_required_fields(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createPermission();

        $this->putJson($this->permissionPath($permission), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'name']);
    }

    public function test_update_nonexistent_permission_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson('/api/permissions/99999', [
            'title' => 'Missing',
            'name' => 'missing-perm',
        ])->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_permission_and_related_pivot_records(): void
    {
        $this->actingAsSuperAdmin();

        $role = $this->createRole(['name' => 'destroy-role-'.Str::uuid(), 'title' => 'Destroy Role']);
        $permission = $this->createPermission(['name' => 'destroy-perm-'.Str::uuid(), 'title' => 'Destroy Perm']);
        $permission->assignRole($role);

        $admin = $this->createRegularAdminRecord();
        $adminPermission = $this->createAdminDirectPermission(['name' => 'admin-linked-perm-'.Str::uuid()]);
        $admin->givePermissionTo($adminPermission);

        DB::table('model_has_permissions')->insert([
            'permission_id' => $permission->id,
            'model_type' => Admin::class,
            'model_id' => $admin->id,
        ]);

        $response = $this->deleteJson($this->permissionPath($permission));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('permissions', ['id' => $permission->id], 'sqlite');
        $this->assertDatabaseMissing('role_has_permissions', ['permission_id' => $permission->id], 'sqlite');
        $this->assertDatabaseMissing('model_has_permissions', ['permission_id' => $permission->id], 'sqlite');
    }

    public function test_destroy_nonexistent_permission_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson('/api/permissions/99999')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Permission not found.');
    }

    public function test_destroy_uses_direct_db_deletion_without_eloquent_events(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createPermission(['name' => 'db-delete-'.Str::uuid(), 'title' => 'DB Delete']);

        $this->deleteJson($this->permissionPath($permission))->assertOk();

        $this->assertSame(0, DB::connection('sqlite')->table('permissions')->where('id', $permission->id)->count());
    }

    public function test_destroy_returns_500_when_transaction_fails(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createPermission(['name' => 'fail-delete-'.Str::uuid(), 'title' => 'Fail Delete']);

        config([
            'permission.table_names.model_has_permissions' => 'missing_model_has_permissions',
            'permission.table_names.role_has_permissions' => 'missing_role_has_permissions',
        ]);

        $response = $this->deleteJson($this->permissionPath($permission))
            ->assertStatus(500)
            ->assertJsonPath('success', false);

        $this->assertStringStartsWith(
            'Failed to delete permission:',
            (string) $response->json('message')
        );
    }

    // -------------------------------------------------------------------------
    // removeRole
    // -------------------------------------------------------------------------

    public function test_remove_role_detaches_role_from_permission(): void
    {
        $this->actingAsSuperAdmin();

        $role = $this->createRole(['name' => 'detach-role-'.Str::uuid(), 'title' => 'Detach Role']);
        $permission = $this->createPermission(['name' => 'detach-perm-'.Str::uuid(), 'title' => 'Detach Perm']);
        $permission->assignRole($role);

        $response = $this->deleteJson("/api/permissions/{$permission->id}/roles/{$role->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::REMOVE_ROLE_SUCCESS_MESSAGE);

        $permission->refresh();
        $this->assertFalse($permission->roles->contains('id', $role->id));
    }

    public function test_remove_role_returns_not_found_for_missing_permission(): void
    {
        $this->actingAsSuperAdmin();

        $role = $this->createRole();

        $this->deleteJson("/api/permissions/99999/roles/{$role->id}")->assertNotFound();
    }

    public function test_remove_role_returns_not_found_for_missing_role(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createPermission();

        $this->deleteJson("/api/permissions/{$permission->id}/roles/99999")->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validStorePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test Permission',
            'name' => 'test-permission-'.Str::uuid(),
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validUpdatePayload(Permission $permission, array $overrides = []): array
    {
        return array_merge([
            'title' => $permission->title,
            'name' => $permission->name,
        ], $overrides);
    }

    private function permissionPath(Permission $permission): string
    {
        return '/api/permissions/'.$permission->id;
    }
}
