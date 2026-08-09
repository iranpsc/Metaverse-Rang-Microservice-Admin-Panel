<?php

namespace Tests\Feature\AccessManagement;

use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesAccessManagementApiSchema;
use Tests\TestCase;

class RolesControllerTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesAccessManagementApiSchema;

    private const INDEX_PATH = '/api/roles';

    private const PERMISSIONS_PATH = '/api/roles/permissions';

    private const INDEX_SUCCESS_MESSAGE = 'Roles retrieved successfully.';

    private const PERMISSIONS_SUCCESS_MESSAGE = 'Permissions retrieved successfully.';

    private const SHOW_SUCCESS_MESSAGE = 'Role retrieved successfully.';

    private const STORE_SUCCESS_MESSAGE = 'Role created successfully.';

    private const UPDATE_SUCCESS_MESSAGE = 'Role updated successfully.';

    private const DESTROY_SUCCESS_MESSAGE = 'Role deleted successfully.';

    private const REMOVE_PERMISSION_SUCCESS_MESSAGE = 'Permission removed from role successfully.';

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
        $role = $this->createRole();

        $this->getJson($this->rolePath($role))->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $role = $this->createRole();

        $this->putJson($this->rolePath($role), $this->validUpdatePayload($role))->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $role = $this->createRole();

        $this->deleteJson($this->rolePath($role))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson(self::PERMISSIONS_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $role = $this->createRole(['name' => 'editor-'.Str::uuid()]);

        $this->getJson($this->rolePath($role))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'creator-'.Str::uuid(),
            'title' => 'Creator Role',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->putJson($this->rolePath($role), $this->validUpdatePayload($role, [
            'title' => 'Updated Role',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $toDelete = $this->createRole(['name' => 'deletable-'.Str::uuid()]);

        $this->deleteJson($this->rolePath($toDelete))
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
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

    public function test_index_returns_paginated_roles_excluding_super_admin(): void
    {
        $this->actingAsSuperAdmin();

        $this->createRole(['name' => 'manager-'.Str::uuid(), 'title' => 'Manager']);
        $this->createRole(['name' => 'support-'.Str::uuid(), 'title' => 'Support']);
        Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'admin'],
            ['title' => 'Super Admin']
        );

        $response = $this->getJson(self::INDEX_PATH.'?per_page=1&page=1');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'roles' => [
                        '*' => [
                            'id',
                            'title',
                            'name',
                            'created_at',
                            'created_at_shamsi',
                            'created_at_time',
                            'permissions',
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
            ]);

        $roleNames = collect($response->json('data.roles'))->pluck('name');

        $this->assertFalse($roleNames->contains('super-admin'));
        $this->assertSame(1, $response->json('data.pagination.per_page'));
    }

    // -------------------------------------------------------------------------
    // getPermissions
    // -------------------------------------------------------------------------

    public function test_get_permissions_returns_all_permissions(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createPermission([
            'name' => 'manage-users',
            'title' => 'Manage Users',
        ]);

        $response = $this->getJson(self::PERMISSIONS_PATH);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::PERMISSIONS_SUCCESS_MESSAGE)
            ->assertJsonFragment([
                'id' => $permission->id,
                'name' => 'manage-users',
                'title' => 'Manage Users',
            ]);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_returns_role_with_permissions_and_available_permissions(): void
    {
        $this->actingAsSuperAdmin();

        $assigned = $this->createPermission(['name' => 'assigned-perm', 'title' => 'Assigned']);
        $available = $this->createPermission(['name' => 'available-perm', 'title' => 'Available']);
        $role = $this->createRole(['name' => 'viewer-'.Str::uuid(), 'title' => 'Viewer']);
        $role->givePermissionTo($assigned);

        $response = $this->getJson($this->rolePath($role));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonPath('data.role.id', $role->id)
            ->assertJsonPath('data.role.name', $role->name)
            ->assertJsonFragment(['name' => 'assigned-perm'])
            ->assertJsonFragment(['name' => 'available-perm']);

        $availablePermissionIds = collect($response->json('data.available_permissions'))->pluck('id');

        $this->assertTrue($availablePermissionIds->contains($available->id));
        $this->assertFalse($availablePermissionIds->contains($assigned->id));
    }

    public function test_show_super_admin_returns_forbidden(): void
    {
        $this->actingAsSuperAdmin();

        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'admin'],
            ['title' => 'Super Admin']
        );

        $this->getJson($this->rolePath($superAdminRole))
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Cannot access super-admin role');
    }

    public function test_show_nonexistent_role_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson('/api/roles/99999')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_role_with_permissions(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createPermission([
            'name' => 'create-posts',
            'title' => 'Create Posts',
        ]);
        $name = 'content-editor-'.Str::uuid();

        $response = $this->postJson(self::INDEX_PATH, [
            'title' => 'Content Editor',
            'name' => $name,
            'permissions' => [$permission->name],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.role.name', $name)
            ->assertJsonPath('data.role.title', 'Content Editor');

        $this->assertDatabaseHas('roles', [
            'name' => $name,
            'title' => 'Content Editor',
        ], 'sqlite');

        $role = Role::where('name', $name)->first();
        $this->assertTrue($role->hasPermissionTo($permission));
    }

    public function test_store_creates_role_without_permissions(): void
    {
        $this->actingAsSuperAdmin();

        $name = 'basic-role-'.Str::uuid();

        $this->postJson(self::INDEX_PATH, [
            'title' => 'Basic Role',
            'name' => $name,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $role = Role::where('name', $name)->first();
        $this->assertCount(0, $role->permissions);
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

    public function test_store_validation_fails_for_duplicate_name(): void
    {
        $this->actingAsSuperAdmin();

        $existing = $this->createRole(['name' => 'duplicate-role', 'title' => 'Duplicate']);

        $response = $this->postJson(self::INDEX_PATH, [
            'title' => 'Another Title',
            'name' => $existing->name,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validation_fails_for_invalid_permission_name(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJson(self::INDEX_PATH, [
            'title' => 'Invalid Perm Role',
            'name' => 'invalid-perm-role-'.Str::uuid(),
            'permissions' => ['nonexistent-permission'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['permissions.0']);
    }

    public function test_store_validation_fails_for_short_title_and_name(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJson(self::INDEX_PATH, [
            'title' => 'A',
            'name' => 'B',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'name']);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_modifies_role_and_syncs_permissions(): void
    {
        $this->actingAsSuperAdmin();

        $oldPermission = $this->createPermission(['name' => 'old-perm', 'title' => 'Old']);
        $newPermission = $this->createPermission(['name' => 'new-perm', 'title' => 'New']);
        $role = $this->createRole(['name' => 'sync-role-'.Str::uuid(), 'title' => 'Sync Role']);
        $role->givePermissionTo($oldPermission);

        $updatedName = 'sync-role-updated-'.Str::uuid();

        $response = $this->putJson($this->rolePath($role), [
            'title' => 'Updated Sync Role',
            'name' => $updatedName,
            'permissions' => [$newPermission->name],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.role.name', $updatedName);

        $role->refresh();
        $this->assertSame('Updated Sync Role', $role->title);
        $this->assertTrue($role->hasPermissionTo($newPermission));
        $this->assertFalse($role->hasPermissionTo($oldPermission));
    }

    public function test_update_with_empty_permissions_clears_role_permissions(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createPermission(['name' => 'to-clear', 'title' => 'To Clear']);
        $role = $this->createRole(['name' => 'clearable-'.Str::uuid(), 'title' => 'Clearable']);
        $role->givePermissionTo($permission);

        $this->putJson($this->rolePath($role), [
            'title' => $role->title,
            'name' => $role->name,
            'permissions' => [],
        ])->assertOk();

        $role->refresh();
        $this->assertCount(0, $role->permissions);
    }

    public function test_update_without_permissions_key_keeps_existing_permissions(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createPermission(['name' => 'keep-me', 'title' => 'Keep Me']);
        $role = $this->createRole(['name' => 'persistent-'.Str::uuid(), 'title' => 'Persistent']);
        $role->givePermissionTo($permission);

        $this->putJson($this->rolePath($role), [
            'title' => 'Persistent Updated',
            'name' => $role->name,
        ])->assertOk();

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo($permission));
        $this->assertSame('Persistent Updated', $role->title);
    }

    public function test_update_super_admin_returns_forbidden(): void
    {
        $this->actingAsSuperAdmin();

        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'admin'],
            ['title' => 'Super Admin']
        );

        $this->putJson($this->rolePath($superAdminRole), [
            'title' => 'Hacked',
            'name' => 'hacked',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Cannot update super-admin role');
    }

    public function test_update_validation_fails_for_duplicate_name(): void
    {
        $this->actingAsSuperAdmin();

        $existing = $this->createRole(['name' => 'taken-name', 'title' => 'Taken']);
        $role = $this->createRole(['name' => 'other-name-'.Str::uuid(), 'title' => 'Other']);

        $this->putJson($this->rolePath($role), [
            'title' => 'Other Updated',
            'name' => $existing->name,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_nonexistent_role_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson('/api/roles/99999', [
            'title' => 'Missing',
            'name' => 'missing',
        ])->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_role_and_pivot_records(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createPermission(['name' => 'linked-perm', 'title' => 'Linked']);
        $role = $this->createRole(['name' => 'deletable-role-'.Str::uuid(), 'title' => 'Deletable']);
        $role->givePermissionTo($permission);

        $admin = $this->createRegularAdminRecord();
        $adminRole = $this->createAdminAssignableRole(['name' => 'admin-linked-role-'.Str::uuid(), 'title' => 'Admin Linked']);
        $admin->assignRole($adminRole);

        DB::table('model_has_roles')->insert([
            'role_id' => $role->id,
            'model_type' => Admin::class,
            'model_id' => $admin->id,
        ]);

        $response = $this->deleteJson($this->rolePath($role));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('roles', ['id' => $role->id], 'sqlite');
        $this->assertDatabaseMissing('role_has_permissions', ['role_id' => $role->id], 'sqlite');
        $this->assertDatabaseMissing('model_has_roles', ['role_id' => $role->id], 'sqlite');
    }

    public function test_destroy_super_admin_returns_forbidden(): void
    {
        $this->actingAsSuperAdmin();

        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'admin'],
            ['title' => 'Super Admin']
        );

        $this->deleteJson($this->rolePath($superAdminRole))
            ->assertForbidden()
            ->assertJsonPath('message', 'Cannot delete super-admin role');
    }

    public function test_destroy_nonexistent_role_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson('/api/roles/99999')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // removePermission
    // -------------------------------------------------------------------------

    public function test_remove_permission_detaches_permission_from_role(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createPermission(['name' => 'detach-me', 'title' => 'Detach Me']);
        $role = $this->createRole(['name' => 'role-with-perm-'.Str::uuid(), 'title' => 'With Perm']);
        $role->givePermissionTo($permission);

        $response = $this->deleteJson("/api/roles/{$role->id}/permissions/{$permission->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::REMOVE_PERMISSION_SUCCESS_MESSAGE);

        $role->refresh();
        $this->assertFalse($role->hasPermissionTo($permission));
    }

    public function test_remove_permission_returns_not_found_for_missing_role(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createPermission();

        $this->deleteJson("/api/roles/99999/permissions/{$permission->id}")->assertNotFound();
    }

    public function test_remove_permission_returns_not_found_for_missing_permission(): void
    {
        $this->actingAsSuperAdmin();

        $role = $this->createRole();

        $this->deleteJson("/api/roles/{$role->id}/permissions/99999")->assertNotFound();
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
            'title' => 'Test Role',
            'name' => 'test-role-'.Str::uuid(),
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validUpdatePayload(Role $role, array $overrides = []): array
    {
        return array_merge([
            'title' => $role->title,
            'name' => $role->name,
        ], $overrides);
    }

    private function rolePath(Role $role): string
    {
        return '/api/roles/'.$role->id;
    }
}
