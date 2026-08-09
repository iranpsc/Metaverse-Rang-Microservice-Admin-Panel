<?php

namespace Tests\Feature\AccessManagement;

use App\Models\Admin;
use App\Notifications\AccountCreatedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesAccessManagementApiSchema;
use Tests\TestCase;

class AdminsControllerTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesAccessManagementApiSchema;

    private const INDEX_PATH = '/api/admins';

    private const EMPLOYEES_PATH = '/api/admins/employees';

    private const ROLES_PATH = '/api/admins/roles';

    private const INDEX_SUCCESS_MESSAGE = 'Admins retrieved successfully.';

    private const EMPLOYEES_SUCCESS_MESSAGE = 'Employees retrieved successfully.';

    private const ROLES_SUCCESS_MESSAGE = 'Roles retrieved successfully.';

    private const SHOW_SUCCESS_MESSAGE = 'Admin retrieved successfully.';

    private const STORE_SUCCESS_MESSAGE = 'Admin created successfully.';

    private const UPDATE_SUCCESS_MESSAGE = 'Admin updated successfully.';

    private const DESTROY_SUCCESS_MESSAGE = 'Admin deleted successfully.';

    private const REMOVE_ROLE_SUCCESS_MESSAGE = 'Role removed from admin successfully.';

    private const REMOVE_PERMISSION_SUCCESS_MESSAGE = 'Permission removed from admin successfully.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAccessManagementApiSchema();
        Notification::fake();
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
        $this->postJson(self::INDEX_PATH, [
            'employee' => 1,
            'roles' => [1],
        ])->assertUnauthorized();
    }

    public function test_unauthenticated_show_returns_unauthorized(): void
    {
        $admin = $this->createRegularAdminRecord();

        $this->getJson($this->adminPath($admin))->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $admin = $this->createRegularAdminRecord();

        $this->putJson($this->adminPath($admin), ['roles' => []])->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $admin = $this->createRegularAdminRecord();

        $this->deleteJson($this->adminPath($admin))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)->assertOk()->assertJsonPath('success', true);
        $this->getJson(self::EMPLOYEES_PATH)->assertOk()->assertJsonPath('success', true);
        $this->getJson(self::ROLES_PATH)->assertOk()->assertJsonPath('success', true);

        $admin = $this->createRegularAdminRecord();
        $this->getJson($this->adminPath($admin))->assertOk()->assertJsonPath('success', true);

        $employee = $this->createEmployee(['email' => Str::uuid().'@new-admin.test']);
        $role = $this->createAdminAssignableRole(['name' => 'creator-role-'.Str::uuid()]);

        $this->postJson(self::INDEX_PATH, [
            'employee' => $employee->id,
            'roles' => [$role->id],
        ])->assertOk()->assertJsonPath('success', true);

        $this->putJson($this->adminPath($admin), [
            'roles' => [$role->id],
        ])->assertOk()->assertJsonPath('success', true);

        $toDelete = $this->createRegularAdminRecord();
        $this->deleteJson($this->adminPath($toDelete))->assertOk()->assertJsonPath('success', true);
    }

    public function test_authenticated_regular_admin_can_index_admins(): void
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

    public function test_index_excludes_current_admin_and_super_admins(): void
    {
        $currentAdmin = $this->actingAsSuperAdmin();

        $visibleAdmin = $this->createRegularAdminRecord(['name' => 'Visible Admin']);
        $role = $this->createAdminAssignableRole(['name' => 'viewer-role-'.Str::uuid(), 'title' => 'Viewer']);
        $visibleAdmin->assignRole($role);

        $superAdminRecord = $this->createSuperAdminRecord(['name' => 'Hidden Super Admin']);

        $response = $this->getJson(self::INDEX_PATH);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'data' => [
                    'admins' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'phone',
                            'created_at',
                            'created_at_shamsi',
                            'created_at_time',
                            'roles',
                            'permissions',
                        ],
                    ],
                ],
            ]);

        $adminIds = collect($response->json('data.admins'))->pluck('id');

        $this->assertTrue($adminIds->contains($visibleAdmin->id));
        $this->assertFalse($adminIds->contains($currentAdmin->id));
        $this->assertFalse($adminIds->contains($superAdminRecord->id));
    }

    // -------------------------------------------------------------------------
    // getEmployees
    // -------------------------------------------------------------------------

    public function test_get_employees_returns_all_employees(): void
    {
        $this->actingAsSuperAdmin();

        $employee = $this->createEmployee([
            'fname' => 'Sara',
            'lname' => 'Ahmadi',
            'email' => 'sara.ahmadi@employee.test',
        ]);

        $response = $this->getJson(self::EMPLOYEES_PATH);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::EMPLOYEES_SUCCESS_MESSAGE)
            ->assertJsonFragment([
                'id' => $employee->id,
                'name' => 'Sara Ahmadi',
                'fname' => 'Sara',
                'lname' => 'Ahmadi',
            ]);
    }

    // -------------------------------------------------------------------------
    // getRoles
    // -------------------------------------------------------------------------

    public function test_get_roles_excludes_super_admin(): void
    {
        $this->actingAsSuperAdmin();

        $role = $this->createAdminAssignableRole(['name' => 'assignable-admin-role-'.Str::uuid(), 'title' => 'Assignable']);
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

    public function test_show_returns_admin_with_available_roles_and_permissions(): void
    {
        $this->actingAsSuperAdmin();

        $assignedRole = $this->createAdminAssignableRole(['name' => 'assigned-admin-role-'.Str::uuid(), 'title' => 'Assigned']);
        $availableRole = $this->createAdminAssignableRole(['name' => 'available-admin-role-'.Str::uuid(), 'title' => 'Available']);
        $directPermission = $this->createAdminDirectPermission(['name' => 'direct-perm-'.Str::uuid(), 'title' => 'Direct']);
        $rolePermission = $this->createPermission(['name' => 'role-perm-'.Str::uuid(), 'title' => 'Via Role', 'guard_name' => 'admin']);
        $availablePermission = $this->createAdminDirectPermission(['name' => 'available-perm-'.Str::uuid(), 'title' => 'Available']);

        $assignedRole->givePermissionTo($rolePermission);

        $admin = $this->createRegularAdminRecord(['name' => 'Detail Admin']);
        $admin->assignRole($assignedRole);
        $admin->givePermissionTo($directPermission);

        $response = $this->getJson($this->adminPath($admin));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonPath('data.admin.id', $admin->id)
            ->assertJsonFragment(['name' => $assignedRole->name])
            ->assertJsonFragment(['name' => $directPermission->name])
            ->assertJsonFragment(['name' => $availableRole->name]);

        $availableRoleIds = collect($response->json('data.available_roles'))->pluck('id');
        $availablePermissionIds = collect($response->json('data.available_permissions'))->pluck('id');

        $this->assertTrue($availableRoleIds->contains($availableRole->id));
        $this->assertFalse($availableRoleIds->contains($assignedRole->id));
        $this->assertTrue($availablePermissionIds->contains($availablePermission->id));
        // available_permissions excludes only permissions granted via roles, not direct assignments.
        $this->assertTrue($availablePermissionIds->contains($directPermission->id));
        $this->assertFalse($availablePermissionIds->contains($rolePermission->id));
    }

    public function test_show_super_admin_returns_forbidden(): void
    {
        $this->actingAsSuperAdmin();

        $superAdmin = $this->createSuperAdminRecord();

        $this->getJson($this->adminPath($superAdmin))
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Cannot access super-admin');
    }

    public function test_show_nonexistent_admin_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson('/api/admins/99999')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_admin_from_employee_and_assigns_roles(): void
    {
        $this->actingAsSuperAdmin();

        $employee = $this->createEmployee([
            'fname' => 'Reza',
            'lname' => 'Moradi',
            'email' => 'reza.moradi@employee.test',
            'phone' => '09123456789',
        ]);
        $role = $this->createAdminAssignableRole(['name' => 'new-admin-role-'.Str::uuid(), 'title' => 'New Admin Role']);

        $response = $this->postJson(self::INDEX_PATH, [
            'employee' => $employee->id,
            'roles' => [$role->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.admin.name', 'Reza Moradi')
            ->assertJsonPath('data.admin.email', 'reza.moradi@employee.test');

        $admin = Admin::where('email', 'reza.moradi@employee.test')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole($role));

        Notification::assertSentTo($admin, AccountCreatedNotification::class);
    }

    public function test_store_validation_fails_for_missing_required_fields(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJson(self::INDEX_PATH, []);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['employee', 'roles']);
    }

    public function test_store_validation_fails_for_invalid_employee(): void
    {
        $this->actingAsSuperAdmin();

        $role = $this->createAdminAssignableRole();

        $response = $this->postJson(self::INDEX_PATH, [
            'employee' => 99999,
            'roles' => [$role->id],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['employee']);
    }

    public function test_store_validation_fails_for_empty_roles_array(): void
    {
        $this->actingAsSuperAdmin();

        $employee = $this->createEmployee();

        $response = $this->postJson(self::INDEX_PATH, [
            'employee' => $employee->id,
            'roles' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['roles']);
    }

    public function test_store_validation_fails_for_invalid_role_id(): void
    {
        $this->actingAsSuperAdmin();

        $employee = $this->createEmployee();

        $response = $this->postJson(self::INDEX_PATH, [
            'employee' => $employee->id,
            'roles' => [99999],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['roles.0']);
    }

    public function test_store_returns_error_when_admin_already_exists_for_employee(): void
    {
        $this->actingAsSuperAdmin();

        $employee = $this->createEmployee(['email' => 'duplicate@employee.test']);
        $this->createRegularAdminRecord(['email' => 'duplicate@employee.test']);
        $role = $this->createAdminAssignableRole();

        $response = $this->postJson(self::INDEX_PATH, [
            'employee' => $employee->id,
            'roles' => [$role->id],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Admin already exists for this employee');
    }

    public function test_store_returns_error_when_no_valid_roles_for_guard(): void
    {
        $this->actingAsSuperAdmin();

        $employee = $this->createEmployee(['email' => Str::uuid().'@guard.test']);
        $invalidRole = Role::create([
            'name' => 'sanctum-only-'.Str::uuid(),
            'title' => 'Sanctum Only',
            'guard_name' => 'sanctum',
        ]);

        $response = $this->postJson(self::INDEX_PATH, [
            'employee' => $employee->id,
            'roles' => [$invalidRole->id],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'No valid roles found for the specified guard');
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_assigns_roles_and_permissions_to_admin(): void
    {
        $this->actingAsSuperAdmin();

        $admin = $this->createRegularAdminRecord();
        $role = $this->createAdminAssignableRole(['name' => 'update-role-'.Str::uuid(), 'title' => 'Update Role']);
        $permission = $this->createAdminDirectPermission(['name' => 'update-perm-'.Str::uuid(), 'title' => 'Update Perm']);

        $response = $this->putJson($this->adminPath($admin), [
            'roles' => [$role->id],
            'permissions' => [$permission->name],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.admin.id', $admin->id);

        $admin->refresh();
        $this->assertTrue($admin->hasRole($role));
        $this->assertTrue($admin->hasDirectPermission($permission));
    }

    public function test_update_super_admin_returns_forbidden(): void
    {
        $this->actingAsSuperAdmin();

        $superAdmin = $this->createSuperAdminRecord();
        $role = $this->createAdminAssignableRole();

        $this->putJson($this->adminPath($superAdmin), [
            'roles' => [$role->id],
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Cannot update super-admin');
    }

    public function test_update_validation_fails_for_invalid_permission_name(): void
    {
        $this->actingAsSuperAdmin();

        $admin = $this->createRegularAdminRecord();

        $this->putJson($this->adminPath($admin), [
            'permissions' => ['nonexistent-permission'],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['permissions.0']);
    }

    public function test_update_validation_fails_for_invalid_role_id(): void
    {
        $this->actingAsSuperAdmin();

        $admin = $this->createRegularAdminRecord();

        $this->putJson($this->adminPath($admin), [
            'roles' => [99999],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['roles.0']);
    }

    public function test_update_returns_error_when_no_valid_roles_for_guard(): void
    {
        $this->actingAsSuperAdmin();

        $admin = $this->createRegularAdminRecord();
        $invalidRole = Role::create([
            'name' => 'invalid-guard-role-'.Str::uuid(),
            'title' => 'Invalid Guard',
            'guard_name' => 'sanctum',
        ]);

        $this->putJson($this->adminPath($admin), [
            'roles' => [$invalidRole->id],
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'No valid roles found for the specified guard');
    }

    public function test_update_nonexistent_admin_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson('/api/admins/99999', [
            'roles' => [],
        ])->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_removes_roles_permissions_and_deletes_admin(): void
    {
        $this->actingAsSuperAdmin();

        $role = $this->createAdminAssignableRole(['name' => 'delete-admin-role-'.Str::uuid(), 'title' => 'Delete Role']);
        $permission = $this->createAdminDirectPermission(['name' => 'delete-admin-perm-'.Str::uuid(), 'title' => 'Delete Perm']);
        $admin = $this->createRegularAdminRecord(['name' => 'Deletable Admin']);
        $admin->assignRole($role);
        $admin->givePermissionTo($permission);

        $response = $this->deleteJson($this->adminPath($admin));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('admins', ['id' => $admin->id], 'sqlite');
        $this->assertDatabaseMissing('model_has_roles', [
            'model_id' => $admin->id,
            'model_type' => Admin::class,
        ], 'sqlite');
        $this->assertDatabaseMissing('model_has_permissions', [
            'model_id' => $admin->id,
            'model_type' => Admin::class,
        ], 'sqlite');
    }

    public function test_destroy_super_admin_returns_forbidden(): void
    {
        $this->actingAsSuperAdmin();

        $superAdmin = $this->createSuperAdminRecord();

        $this->deleteJson($this->adminPath($superAdmin))
            ->assertForbidden()
            ->assertJsonPath('message', 'Cannot delete super-admin');
    }

    public function test_destroy_nonexistent_admin_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson('/api/admins/99999')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // removeRole
    // -------------------------------------------------------------------------

    public function test_remove_role_detaches_role_from_admin(): void
    {
        $this->actingAsSuperAdmin();

        $role = $this->createAdminAssignableRole(['name' => 'remove-admin-role-'.Str::uuid(), 'title' => 'Remove Role']);
        $admin = $this->createRegularAdminRecord();
        $admin->assignRole($role);

        $response = $this->deleteJson("/api/admins/{$admin->id}/roles/{$role->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::REMOVE_ROLE_SUCCESS_MESSAGE);

        $admin->refresh();
        $this->assertFalse($admin->hasRole($role));
    }

    public function test_remove_role_returns_not_found_for_missing_admin(): void
    {
        $this->actingAsSuperAdmin();

        $role = $this->createAdminAssignableRole();

        $this->deleteJson("/api/admins/99999/roles/{$role->id}")->assertNotFound();
    }

    public function test_remove_role_returns_not_found_for_missing_role(): void
    {
        $this->actingAsSuperAdmin();

        $admin = $this->createRegularAdminRecord();

        $this->deleteJson("/api/admins/{$admin->id}/roles/99999")->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // removePermission
    // -------------------------------------------------------------------------

    public function test_remove_permission_revokes_direct_permission_from_admin(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createAdminDirectPermission(['name' => 'revoke-perm-'.Str::uuid(), 'title' => 'Revoke Perm']);
        $admin = $this->createRegularAdminRecord();
        $admin->givePermissionTo($permission);

        $response = $this->deleteJson("/api/admins/{$admin->id}/permissions/{$permission->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::REMOVE_PERMISSION_SUCCESS_MESSAGE);

        $admin->refresh();
        $this->assertFalse($admin->hasDirectPermission($permission));
    }

    public function test_remove_permission_returns_not_found_for_missing_admin(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createAdminDirectPermission();

        $this->deleteJson("/api/admins/99999/permissions/{$permission->id}")->assertNotFound();
    }

    public function test_remove_permission_returns_not_found_for_missing_permission(): void
    {
        $this->actingAsSuperAdmin();

        $admin = $this->createRegularAdminRecord();

        $this->deleteJson("/api/admins/{$admin->id}/permissions/99999")->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function adminPath(Admin $admin): string
    {
        return '/api/admins/'.$admin->id;
    }
}
