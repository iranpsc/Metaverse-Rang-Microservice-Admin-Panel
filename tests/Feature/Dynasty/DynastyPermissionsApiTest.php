<?php

namespace Tests\Feature\Dynasty;

use App\Models\Dynasty\DynastyPermission;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesDynastyApiSchema;
use Tests\TestCase;

class DynastyPermissionsApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesDynastyApiSchema;

    private const SHOW_PATH = '/api/dynasty/permissions';

    private const SHOW_SUCCESS_MESSAGE = 'دسترسی‌های سلسله با موفقیت بارگذاری شدند.';

    private const UPDATE_SUCCESS_MESSAGE = 'اطلاعات با موفقیت ثبت شد';

    /** @var list<string> */
    private const FLAGS = [
        'BFR',
        'SF',
        'W',
        'JU',
        'DM',
        'PIUP',
        'PITC',
        'PIC',
        'ESOO',
        'COTB',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDynastyApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_show_returns_unauthorized(): void
    {
        $this->getJson(self::SHOW_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $this->putJson(self::SHOW_PATH, $this->validDynastyPermissionUpdatePayload())
            ->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::SHOW_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE);

        $this->putJson(self::SHOW_PATH, $this->validDynastyPermissionUpdatePayload())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::SHOW_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->putJson(self::SHOW_PATH, $this->validDynastyPermissionUpdatePayload([
            'BFR' => false,
            'SF' => true,
        ]))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Happy path / Show
    // -------------------------------------------------------------------------

    public function test_show_returns_defaults_when_no_record_exists(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->getJson(self::SHOW_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonPath('data.id', null)
            ->assertJsonPath('data.created_at', null)
            ->assertJsonPath('data.updated_at', null);

        foreach (self::FLAGS as $flag) {
            $this->assertSame(0, $response->json("data.{$flag}"));
        }

        $this->assertDatabaseCount('dynasty_permissions', 0);
    }

    public function test_show_returns_existing_permissions(): void
    {
        $this->actingAsSuperAdmin();

        $permission = $this->createDynastyPermission([
            'id' => 1,
            'BFR' => 1,
            'SF' => 0,
            'W' => 1,
            'JU' => 0,
            'DM' => 1,
            'PIUP' => 0,
            'PITC' => 1,
            'PIC' => 0,
            'ESOO' => 1,
            'COTB' => 0,
        ]);

        $this->getJson(self::SHOW_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonPath('data.id', $permission->id)
            ->assertJsonPath('data.BFR', 1)
            ->assertJsonPath('data.SF', 0)
            ->assertJsonPath('data.W', 1)
            ->assertJsonPath('data.JU', 0)
            ->assertJsonPath('data.DM', 1)
            ->assertJsonPath('data.PIUP', 0)
            ->assertJsonPath('data.PITC', 1)
            ->assertJsonPath('data.PIC', 0)
            ->assertJsonPath('data.ESOO', 1)
            ->assertJsonPath('data.COTB', 0)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => array_merge(['id', 'created_at', 'updated_at'], self::FLAGS),
            ]);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_creates_permissions_record_when_missing(): void
    {
        $this->actingAsSuperAdmin();

        $this->assertDatabaseCount('dynasty_permissions', 0);

        $response = $this->putJson(self::SHOW_PATH, $this->validDynastyPermissionUpdatePayload([
            'BFR' => true,
            'SF' => true,
            'W' => false,
            'JU' => false,
            'DM' => true,
            'PIUP' => false,
            'PITC' => false,
            'PIC' => true,
            'ESOO' => false,
            'COTB' => true,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.id', 1)
            ->assertJsonPath('data.BFR', 1)
            ->assertJsonPath('data.SF', 1)
            ->assertJsonPath('data.W', 0)
            ->assertJsonPath('data.JU', 0)
            ->assertJsonPath('data.DM', 1)
            ->assertJsonPath('data.PIUP', 0)
            ->assertJsonPath('data.PITC', 0)
            ->assertJsonPath('data.PIC', 1)
            ->assertJsonPath('data.ESOO', 0)
            ->assertJsonPath('data.COTB', 1);

        $this->assertDatabaseHas('dynasty_permissions', [
            'id' => 1,
            'BFR' => 1,
            'SF' => 1,
            'W' => 0,
            'JU' => 0,
            'DM' => 1,
            'PIUP' => 0,
            'PITC' => 0,
            'PIC' => 1,
            'ESOO' => 0,
            'COTB' => 1,
        ]);

        $this->assertNotNull($response->json('data.created_at'));
        $this->assertNotNull($response->json('data.updated_at'));
    }

    public function test_update_updates_existing_permissions_record(): void
    {
        $this->actingAsSuperAdmin();

        $this->createDynastyPermission([
            'id' => 1,
            'BFR' => 0,
            'SF' => 0,
            'W' => 0,
            'JU' => 0,
            'DM' => 0,
            'PIUP' => 0,
            'PITC' => 0,
            'PIC' => 0,
            'ESOO' => 0,
            'COTB' => 0,
        ]);

        $this->putJson(self::SHOW_PATH, [
            'BFR' => true,
            'SF' => true,
            'W' => true,
            'JU' => true,
            'DM' => true,
            'PIUP' => true,
            'PITC' => true,
            'PIC' => true,
            'ESOO' => true,
            'COTB' => true,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.id', 1);

        $this->assertDatabaseHas('dynasty_permissions', [
            'id' => 1,
            'BFR' => 1,
            'SF' => 1,
            'W' => 1,
            'JU' => 1,
            'DM' => 1,
            'PIUP' => 1,
            'PITC' => 1,
            'PIC' => 1,
            'ESOO' => 1,
            'COTB' => 1,
        ]);

        $this->assertDatabaseCount('dynasty_permissions', 1);
    }

    public function test_update_coerces_boolean_like_values_to_zero_or_one(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson(self::SHOW_PATH, [
            'BFR' => 1,
            'SF' => 0,
            'W' => '1',
            'JU' => '0',
            'DM' => true,
            'PIUP' => false,
            'PITC' => 1,
            'PIC' => 0,
            'ESOO' => true,
            'COTB' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.BFR', 1)
            ->assertJsonPath('data.SF', 0)
            ->assertJsonPath('data.W', 1)
            ->assertJsonPath('data.JU', 0)
            ->assertJsonPath('data.DM', 1)
            ->assertJsonPath('data.PIUP', 0)
            ->assertJsonPath('data.PITC', 1)
            ->assertJsonPath('data.PIC', 0)
            ->assertJsonPath('data.ESOO', 1)
            ->assertJsonPath('data.COTB', 0);
    }

    public function test_update_omitted_fields_default_to_zero(): void
    {
        $this->actingAsSuperAdmin();

        $this->createDynastyPermission([
            'id' => 1,
            'BFR' => 1,
            'SF' => 1,
            'W' => 1,
            'JU' => 1,
            'DM' => 1,
            'PIUP' => 1,
            'PITC' => 1,
            'PIC' => 1,
            'ESOO' => 1,
            'COTB' => 1,
        ]);

        $this->putJson(self::SHOW_PATH, [
            'BFR' => true,
        ])
            ->assertOk()
            ->assertJsonPath('data.BFR', 1)
            ->assertJsonPath('data.SF', 0)
            ->assertJsonPath('data.W', 0)
            ->assertJsonPath('data.JU', 0)
            ->assertJsonPath('data.DM', 0)
            ->assertJsonPath('data.PIUP', 0)
            ->assertJsonPath('data.PITC', 0)
            ->assertJsonPath('data.PIC', 0)
            ->assertJsonPath('data.ESOO', 0)
            ->assertJsonPath('data.COTB', 0);

        $this->assertDatabaseHas('dynasty_permissions', [
            'id' => 1,
            'BFR' => 1,
            'SF' => 0,
            'W' => 0,
            'JU' => 0,
            'DM' => 0,
            'PIUP' => 0,
            'PITC' => 0,
            'PIC' => 0,
            'ESOO' => 0,
            'COTB' => 0,
        ]);
    }

    public function test_update_empty_payload_sets_all_flags_to_zero(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->putJson(self::SHOW_PATH, [])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.id', 1);

        foreach (self::FLAGS as $flag) {
            $this->assertSame(0, $response->json("data.{$flag}"));
        }
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_update_rejects_non_boolean_flag_values(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson(self::SHOW_PATH, [
            'BFR' => 'yes',
            'SF' => 'no',
            'W' => 'maybe',
            'JU' => 2,
            'DM' => 'true',
            'PIUP' => 'false',
            'PITC' => [],
            'PIC' => 'on',
            'ESOO' => 'off',
            'COTB' => 5,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(self::FLAGS);
    }

    public function test_show_uses_dynasty_permissions_table_not_spatie_permissions(): void
    {
        $this->actingAsSuperAdmin();

        $this->assertTrue(Schema::hasTable('dynasty_permissions'));
        $this->assertTrue(Schema::hasTable('permissions'));

        $this->createDynastyPermission([
            'id' => 1,
            'BFR' => 1,
        ]);

        $this->getJson(self::SHOW_PATH)
            ->assertOk()
            ->assertJsonPath('data.BFR', 1);

        $this->assertDatabaseHas('dynasty_permissions', ['id' => 1, 'BFR' => 1]);
        $this->assertDatabaseCount('dynasty_permissions', 1);
        $this->assertSame('dynasty_permissions', (new DynastyPermission)->getTable());
    }

    // -------------------------------------------------------------------------
    // Error paths
    // -------------------------------------------------------------------------

    public function test_show_returns_500_when_query_fails(): void
    {
        $this->actingAsSuperAdmin();

        Schema::drop('dynasty_permissions');

        $this->getJson(self::SHOW_PATH)
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در بارگذاری دسترسی‌های سلسله');
    }

    public function test_update_returns_500_when_persist_fails(): void
    {
        $this->actingAsSuperAdmin();

        DynastyPermission::saving(function () {
            throw new \RuntimeException('forced permission save failure');
        });

        $this->putJson(self::SHOW_PATH, [
            'BFR' => true,
            'SF' => false,
        ])
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در بروزرسانی اطلاعات');
    }
}
