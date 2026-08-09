<?php

namespace Tests\Feature\LevelLicense;

use App\Models\Level\Level;
use App\Models\Level\LevelLicense;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesLevelLicenseApiSchema;
use Tests\TestCase;

class LevelLicenseApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesLevelLicenseApiSchema;

    private const SHOW_SUCCESS_MESSAGE = 'مجوزهای سطح با موفقیت دریافت شد.';

    private const SHOW_EMPTY_MESSAGE = 'برای این سطح تاکنون مجوزی ثبت نشده است.';

    private const STORE_SUCCESS_MESSAGE = 'مجوزهای سطح با موفقیت ثبت شد.';

    private const STORE_ALREADY_EXISTS_MESSAGE = 'برای این سطح مجوز ثبت شده است. لطفاً از ویرایش استفاده کنید.';

    private const UPDATE_SUCCESS_MESSAGE = 'مجوزهای سطح با موفقیت بروزرسانی شد.';

    private const UPDATE_MISSING_MESSAGE = 'برای این سطح مجوزی ثبت نشده است.';

    /**
     * @var list<string>
     */
    private const LICENSE_BOOLEAN_FIELDS = [
        'create_union',
        'add_memeber_to_union',
        'observation_license',
        'gate_license',
        'lawyer_license',
        'city_counsile_entry',
        'establish_special_residential_property',
        'establish_property_on_surface',
        'judge_entry',
        'upload_image',
        'delete_image',
        'inter_level_general_points',
        'inter_level_special_points',
        'rent_out_satisfaction',
        'access_to_answer_questions_unit',
        'create_challenge_questions',
        'upload_music',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLevelLicenseApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_show_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->getJson($this->licensesPath($level))->assertUnauthorized();
    }

    public function test_unauthenticated_store_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->postJson($this->licensesPath($level), $this->validPayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->putJson($this->licensesPath($level), $this->validPayload())
            ->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->getJson($this->licensesPath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_EMPTY_MESSAGE)
            ->assertJsonPath('data.licenses', null);

        $this->postJson($this->licensesPath($level), $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $this->putJson($this->licensesPath($level), $this->validPayload([
            'create_union' => false,
            'upload_music' => true,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $level = Level::factory()->create();

        $this->getJson($this->licensesPath($level))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->postJson($this->licensesPath($level), $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->putJson($this->licensesPath($level), $this->validPayload([
            'gate_license' => false,
        ]))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_returns_null_licenses_when_none_exist(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->getJson($this->licensesPath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_EMPTY_MESSAGE)
            ->assertJsonPath('data.licenses', null)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'licenses',
                ],
            ]);
    }

    public function test_show_returns_full_license_resource_structure(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $licenses = LevelLicense::factory()->allEnabled()->create([
            'level_id' => $level->id,
        ]);

        $this->getJson($this->licensesPath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonPath('data.licenses.id', $licenses->id)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'licenses' => array_merge(
                        ['id', 'created_at', 'updated_at'],
                        self::LICENSE_BOOLEAN_FIELDS
                    ),
                ],
            ]);
    }

    public function test_show_casts_boolean_fields_in_response(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelLicense::factory()->create([
            'level_id' => $level->id,
            'create_union' => 1,
            'add_memeber_to_union' => 0,
            'observation_license' => true,
            'gate_license' => false,
            'lawyer_license' => true,
            'city_counsile_entry' => false,
            'establish_special_residential_property' => true,
            'establish_property_on_surface' => false,
            'judge_entry' => true,
            'upload_image' => false,
            'delete_image' => true,
            'inter_level_general_points' => false,
            'inter_level_special_points' => true,
            'rent_out_satisfaction' => false,
            'access_to_answer_questions_unit' => true,
            'create_challenge_questions' => false,
            'upload_music' => true,
        ]);

        $response = $this->getJson($this->licensesPath($level))->assertOk();

        $this->assertTrue($response->json('data.licenses.create_union'));
        $this->assertFalse($response->json('data.licenses.add_memeber_to_union'));
        $this->assertTrue($response->json('data.licenses.observation_license'));
        $this->assertFalse($response->json('data.licenses.gate_license'));
        $this->assertTrue($response->json('data.licenses.upload_music'));
    }

    public function test_show_returns_iso8601_timestamps(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $licenses = LevelLicense::factory()->create(['level_id' => $level->id]);

        $this->getJson($this->licensesPath($level))
            ->assertOk()
            ->assertJsonPath('data.licenses.created_at', $licenses->created_at->toISOString())
            ->assertJsonPath('data.licenses.updated_at', $licenses->updated_at->toISOString());
    }

    public function test_show_does_not_leak_other_level_licenses(): void
    {
        $this->actingAsSuperAdmin();

        $levelA = Level::factory()->create();
        $levelB = Level::factory()->create();

        $licensesA = LevelLicense::factory()->allEnabled()->create(['level_id' => $levelA->id]);
        LevelLicense::factory()->allDisabled()->create(['level_id' => $levelB->id]);

        $this->getJson($this->licensesPath($levelA))
            ->assertOk()
            ->assertJsonPath('data.licenses.id', $licensesA->id)
            ->assertJsonPath('data.licenses.create_union', true);

        $this->getJson($this->licensesPath($levelB))
            ->assertOk()
            ->assertJsonPath('data.licenses.create_union', false);
    }

    public function test_show_nonexistent_level_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson($this->licensesPath(999999))->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Store happy path
    // -------------------------------------------------------------------------

    public function test_store_creates_licenses_and_returns_201_with_resource(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $payload = $this->validPayload([
            'create_union' => true,
            'upload_music' => false,
        ]);

        $response = $this->postJson($this->licensesPath($level), $payload)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.licenses.create_union', true)
            ->assertJsonPath('data.licenses.upload_music', false)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'licenses' => array_merge(
                        ['id', 'created_at', 'updated_at'],
                        self::LICENSE_BOOLEAN_FIELDS
                    ),
                ],
            ]);

        $this->assertDatabaseHas('level_licenses', [
            'id' => $response->json('data.licenses.id'),
            'level_id' => $level->id,
            'create_union' => 1,
            'upload_music' => 0,
        ]);
        $this->assertDatabaseCount('level_licenses', 1);
    }

    public function test_store_persists_all_boolean_fields(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $payload = $this->allEnabledPayload();

        $this->postJson($this->licensesPath($level), $payload)->assertCreated();

        $row = LevelLicense::query()->where('level_id', $level->id)->firstOrFail();

        foreach (self::LICENSE_BOOLEAN_FIELDS as $field) {
            $this->assertTrue($row->{$field}, "Expected {$field} to be true");
        }
    }

    public function test_store_accepts_integer_zero_one_booleans(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $payload = $this->validPayload([
            'create_union' => 1,
            'add_memeber_to_union' => 0,
            'observation_license' => 1,
            'gate_license' => 0,
            'lawyer_license' => 1,
            'city_counsile_entry' => 0,
            'establish_special_residential_property' => 1,
            'establish_property_on_surface' => 0,
            'judge_entry' => 1,
            'upload_image' => 0,
            'delete_image' => 1,
            'inter_level_general_points' => 0,
            'inter_level_special_points' => 1,
            'rent_out_satisfaction' => 0,
            'access_to_answer_questions_unit' => 1,
            'create_challenge_questions' => 0,
            'upload_music' => 1,
        ]);

        $this->postJson($this->licensesPath($level), $payload)
            ->assertCreated()
            ->assertJsonPath('data.licenses.create_union', true)
            ->assertJsonPath('data.licenses.add_memeber_to_union', false)
            ->assertJsonPath('data.licenses.upload_music', true);
    }

    public function test_store_accepts_string_zero_one_boolean_forms(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $payload = $this->validPayload([
            'create_union' => '1',
            'add_memeber_to_union' => '0',
            'observation_license' => '1',
            'gate_license' => '0',
        ]);

        $this->postJson($this->licensesPath($level), $payload)
            ->assertCreated()
            ->assertJsonPath('data.licenses.create_union', true)
            ->assertJsonPath('data.licenses.add_memeber_to_union', false)
            ->assertJsonPath('data.licenses.observation_license', true)
            ->assertJsonPath('data.licenses.gate_license', false);
    }

    public function test_store_rejects_wordy_string_boolean_forms(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->postJson($this->licensesPath($level), $this->validPayload([
            'create_union' => 'true',
            'add_memeber_to_union' => 'false',
            'lawyer_license' => 'yes',
            'city_counsile_entry' => 'no',
            'establish_special_residential_property' => 'on',
            'establish_property_on_surface' => 'off',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'create_union',
                'add_memeber_to_union',
                'lawyer_license',
                'city_counsile_entry',
                'establish_special_residential_property',
                'establish_property_on_surface',
            ]);

        $this->assertDatabaseCount('level_licenses', 0);
    }

    public function test_store_rejects_when_licenses_already_exist(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelLicense::factory()->create(['level_id' => $level->id]);

        $this->postJson($this->licensesPath($level), $this->validPayload())
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::STORE_ALREADY_EXISTS_MESSAGE)
            ->assertJsonMissing(['data']);

        $this->assertDatabaseCount('level_licenses', 1);
    }

    public function test_store_nonexistent_level_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson($this->licensesPath(999999), $this->validPayload())
            ->assertNotFound();

        $this->assertDatabaseCount('level_licenses', 0);
    }

    // -------------------------------------------------------------------------
    // Store validation
    // -------------------------------------------------------------------------

    public function test_store_requires_all_boolean_fields(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->postJson($this->licensesPath($level), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(self::LICENSE_BOOLEAN_FIELDS);

        $this->assertDatabaseCount('level_licenses', 0);
    }

    public function test_store_rejects_missing_individual_fields(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        foreach (self::LICENSE_BOOLEAN_FIELDS as $field) {
            $payload = $this->validPayload();
            unset($payload[$field]);

            $this->postJson($this->licensesPath($level), $payload)
                ->assertStatus(422)
                ->assertJsonValidationErrors([$field]);
        }

        $this->assertDatabaseCount('level_licenses', 0);
    }

    public function test_store_rejects_null_boolean_fields(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->postJson($this->licensesPath($level), $this->validPayload([
            'create_union' => null,
            'upload_music' => null,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['create_union', 'upload_music']);

        $this->assertDatabaseCount('level_licenses', 0);
    }

    public function test_store_rejects_non_boolean_values(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->postJson($this->licensesPath($level), $this->validPayload([
            'create_union' => 'not-a-boolean',
            'gate_license' => ['nested'],
            'upload_music' => 2,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['create_union', 'gate_license', 'upload_music']);

        $this->assertDatabaseCount('level_licenses', 0);
    }

    public function test_store_does_not_create_when_validation_fails(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelLicense::factory()->create([
            'level_id' => Level::factory()->create()->id,
        ]);

        $this->postJson($this->licensesPath($level), ['create_union' => true])
            ->assertStatus(422);

        $this->assertDatabaseCount('level_licenses', 1);
        $this->assertDatabaseMissing('level_licenses', ['level_id' => $level->id]);
    }

    public function test_store_ignores_mass_assignment_of_level_id_and_id(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $otherLevel = Level::factory()->create();

        $response = $this->postJson($this->licensesPath($level), $this->validPayload([
            'id' => 99999,
            'level_id' => $otherLevel->id,
            'created_at' => '2000-01-01 00:00:00',
        ]))
            ->assertCreated();

        $this->assertSame($level->id, (int) LevelLicense::query()->findOrFail(
            $response->json('data.licenses.id')
        )->level_id);
        $this->assertNotSame(99999, $response->json('data.licenses.id'));
        $this->assertDatabaseMissing('level_licenses', [
            'level_id' => $otherLevel->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Update happy path
    // -------------------------------------------------------------------------

    public function test_update_changes_licenses_and_returns_resource(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $licenses = LevelLicense::factory()->allDisabled()->create([
            'level_id' => $level->id,
        ]);

        $payload = $this->allEnabledPayload();

        $this->putJson($this->licensesPath($level), $payload)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.licenses.id', $licenses->id)
            ->assertJsonPath('data.licenses.create_union', true)
            ->assertJsonPath('data.licenses.upload_music', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'licenses' => array_merge(
                        ['id', 'created_at', 'updated_at'],
                        self::LICENSE_BOOLEAN_FIELDS
                    ),
                ],
            ]);

        $licenses->refresh();

        foreach (self::LICENSE_BOOLEAN_FIELDS as $field) {
            $this->assertTrue($licenses->{$field}, "Expected {$field} to be true after update");
        }
    }

    public function test_update_persists_partial_boolean_flips(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $licenses = LevelLicense::factory()->create([
            'level_id' => $level->id,
            'create_union' => true,
            'upload_music' => true,
            'gate_license' => true,
        ] + $this->validPayload());

        $this->putJson($this->licensesPath($level), $this->validPayload([
            'create_union' => false,
            'upload_music' => false,
            'gate_license' => true,
        ]))->assertOk();

        $this->assertDatabaseHas('level_licenses', [
            'id' => $licenses->id,
            'level_id' => $level->id,
            'create_union' => 0,
            'upload_music' => 0,
            'gate_license' => 1,
        ]);
    }

    public function test_update_returns_404_when_licenses_missing(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->putJson($this->licensesPath($level), $this->validPayload())
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::UPDATE_MISSING_MESSAGE);

        $this->assertDatabaseCount('level_licenses', 0);
    }

    public function test_update_nonexistent_level_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->licensesPath(999999), $this->validPayload())
            ->assertNotFound();
    }

    public function test_update_does_not_affect_other_level_licenses(): void
    {
        $this->actingAsSuperAdmin();

        $levelA = Level::factory()->create();
        $levelB = Level::factory()->create();

        $licensesA = LevelLicense::factory()->allDisabled()->create(['level_id' => $levelA->id]);
        $licensesB = LevelLicense::factory()->allDisabled()->create(['level_id' => $levelB->id]);

        $this->putJson($this->licensesPath($levelA), $this->allEnabledPayload())
            ->assertOk();

        $licensesA->refresh();
        $licensesB->refresh();

        $this->assertTrue($licensesA->create_union);
        $this->assertFalse($licensesB->create_union);
        $this->assertFalse($licensesB->upload_music);
    }

    // -------------------------------------------------------------------------
    // Update validation
    // -------------------------------------------------------------------------

    public function test_update_requires_all_boolean_fields(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $licenses = LevelLicense::factory()->allEnabled()->create(['level_id' => $level->id]);

        $this->putJson($this->licensesPath($level), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(self::LICENSE_BOOLEAN_FIELDS);

        $licenses->refresh();
        $this->assertTrue($licenses->create_union);
    }

    public function test_update_rejects_non_boolean_values(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelLicense::factory()->create(['level_id' => $level->id] + $this->validPayload());

        $this->putJson($this->licensesPath($level), $this->validPayload([
            'create_union' => 'maybe',
            'upload_image' => 5,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['create_union', 'upload_image']);
    }

    public function test_update_does_not_mutate_when_validation_fails(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $licenses = LevelLicense::factory()->allDisabled()->create(['level_id' => $level->id]);

        $this->putJson($this->licensesPath($level), [
            'create_union' => true,
        ])->assertStatus(422);

        $licenses->refresh();

        foreach (self::LICENSE_BOOLEAN_FIELDS as $field) {
            $this->assertFalse($licenses->{$field}, "Expected {$field} to remain false");
        }
    }

    public function test_update_ignores_mass_assignment_of_level_id_and_id(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $otherLevel = Level::factory()->create();
        $licenses = LevelLicense::factory()->allDisabled()->create(['level_id' => $level->id]);
        $originalId = $licenses->id;

        $this->putJson($this->licensesPath($level), $this->allEnabledPayload([
            'id' => 88888,
            'level_id' => $otherLevel->id,
        ]))->assertOk();

        $licenses->refresh();

        $this->assertSame($originalId, $licenses->id);
        $this->assertSame($level->id, $licenses->level_id);
        $this->assertTrue($licenses->create_union);
        $this->assertDatabaseMissing('level_licenses', [
            'id' => $originalId,
            'level_id' => $otherLevel->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Edge / security
    // -------------------------------------------------------------------------

    public function test_show_after_store_returns_created_licenses(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $storeResponse = $this->postJson($this->licensesPath($level), $this->allEnabledPayload())
            ->assertCreated();

        $licenseId = $storeResponse->json('data.licenses.id');

        $this->getJson($this->licensesPath($level))
            ->assertOk()
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonPath('data.licenses.id', $licenseId)
            ->assertJsonPath('data.licenses.create_union', true);
    }

    public function test_update_then_show_reflects_latest_values(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelLicense::factory()->allEnabled()->create(['level_id' => $level->id]);

        $this->putJson($this->licensesPath($level), $this->allDisabledPayload())
            ->assertOk();

        $this->getJson($this->licensesPath($level))
            ->assertOk()
            ->assertJsonPath('data.licenses.create_union', false)
            ->assertJsonPath('data.licenses.upload_music', false)
            ->assertJsonPath('data.licenses.gate_license', false);
    }

    public function test_invalid_level_id_type_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson('/api/levels/not-an-id/licenses')->assertNotFound();
    }

    public function test_store_returns_500_when_transaction_fails(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        DB::partialMock()
            ->shouldReceive('transaction')
            ->once()
            ->andThrow(new \RuntimeException('forced license store failure'));

        $this->postJson($this->licensesPath($level), $this->validPayload())
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در ثبت مجوزهای سطح');
    }

    public function test_update_returns_500_when_transaction_fails(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelLicense::factory()->create(['level_id' => $level->id]);

        DB::partialMock()
            ->shouldReceive('transaction')
            ->once()
            ->andThrow(new \RuntimeException('forced license update failure'));

        $this->putJson($this->licensesPath($level), $this->validPayload())
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در بروزرسانی مجوزهای سطح');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function licensesPath(int|Level $level): string
    {
        $id = $level instanceof Level ? $level->id : $level;

        return '/api/levels/'.$id.'/licenses';
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge($this->allDisabledPayload(), $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, bool>
     */
    private function allEnabledPayload(array $overrides = []): array
    {
        return array_merge(
            array_fill_keys(self::LICENSE_BOOLEAN_FIELDS, true),
            $overrides
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, bool>
     */
    private function allDisabledPayload(array $overrides = []): array
    {
        return array_merge(
            array_fill_keys(self::LICENSE_BOOLEAN_FIELDS, false),
            $overrides
        );
    }
}
