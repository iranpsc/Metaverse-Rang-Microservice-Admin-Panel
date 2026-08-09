<?php

namespace Tests\Feature\LevelPrize;

use App\Models\Level\Level;
use App\Models\Level\LevelPrize;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesLevelPrizeApiSchema;
use Tests\TestCase;

class LevelPrizeApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesLevelPrizeApiSchema;

    private const SHOW_SUCCESS_MESSAGE = 'اطلاعات پاداش سطح با موفقیت دریافت شد.';

    private const SHOW_EMPTY_MESSAGE = 'برای این سطح تاکنون پاداشی ثبت نشده است.';

    private const STORE_SUCCESS_MESSAGE = 'پاداش سطح با موفقیت ثبت شد.';

    private const STORE_DUPLICATE_MESSAGE = 'برای این سطح پاداشی ثبت شده است. لطفاً از ویرایش استفاده کنید.';

    private const STORE_ERROR_MESSAGE = 'خطا در ثبت پاداش سطح';

    private const UPDATE_SUCCESS_MESSAGE = 'پاداش سطح با موفقیت بروزرسانی شد.';

    private const UPDATE_MISSING_MESSAGE = 'برای این سطح پاداشی ثبت نشده است.';

    private const UPDATE_ERROR_MESSAGE = 'خطا در بروزرسانی پاداش سطح';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLevelPrizeApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_show_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->getJson($this->prizePath($level))->assertUnauthorized();
    }

    public function test_unauthenticated_store_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->postJson($this->prizePath($level), $this->validPayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->putJson($this->prizePath($level), $this->validPayload())
            ->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $levelForShow = Level::factory()->create();
        LevelPrize::factory()->create([
            'level_id' => $levelForShow->id,
            'psc' => 10,
        ]);

        $this->getJson($this->prizePath($levelForShow))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE);

        $levelForStore = Level::factory()->create();

        $this->postJson($this->prizePath($levelForStore), $this->validPayload([
            'psc' => 25,
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $levelForUpdate = Level::factory()->create();
        LevelPrize::factory()->create(['level_id' => $levelForUpdate->id]);

        $this->putJson($this->prizePath($levelForUpdate), $this->validPayload([
            'psc' => 99,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $levelForShow = Level::factory()->create();
        LevelPrize::factory()->create(['level_id' => $levelForShow->id]);

        $this->getJson($this->prizePath($levelForShow))
            ->assertOk()
            ->assertJsonPath('success', true);

        $levelForStore = Level::factory()->create();

        $this->postJson($this->prizePath($levelForStore), $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('success', true);

        $levelForUpdate = Level::factory()->create();
        LevelPrize::factory()->create(['level_id' => $levelForUpdate->id]);

        $this->putJson($this->prizePath($levelForUpdate), $this->validPayload([
            'yellow' => 7,
        ]))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_returns_null_prize_when_none_registered(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->getJson($this->prizePath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_EMPTY_MESSAGE)
            ->assertJsonPath('data.prize', null);
    }

    public function test_show_returns_full_json_structure_when_prize_exists(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelPrize::factory()->create([
            'level_id' => $level->id,
            'psc' => 100,
            'yellow' => 1,
            'blue' => 2,
            'red' => 3,
            'effect' => 4,
            'satisfaction' => 1.2500,
        ]);

        $this->getJson($this->prizePath($level))
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'prize' => [
                        'id',
                        'psc',
                        'yellow',
                        'blue',
                        'red',
                        'effect',
                        'satisfaction',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonPath('data.prize.psc', 100)
            ->assertJsonPath('data.prize.yellow', 1)
            ->assertJsonPath('data.prize.blue', 2)
            ->assertJsonPath('data.prize.red', 3)
            ->assertJsonPath('data.prize.effect', 4)
            ->assertJsonPath('data.prize.satisfaction', 1.25);
    }

    public function test_show_casts_numeric_fields_to_expected_types(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $prize = LevelPrize::factory()->create([
            'level_id' => $level->id,
            'psc' => 42,
            'satisfaction' => 3.1415,
        ]);

        $response = $this->getJson($this->prizePath($level))->assertOk();

        $payload = $response->json('data.prize');

        $this->assertIsInt($payload['id']);
        $this->assertIsInt($payload['psc']);
        $this->assertIsInt($payload['yellow']);
        $this->assertIsInt($payload['blue']);
        $this->assertIsInt($payload['red']);
        $this->assertIsInt($payload['effect']);
        $this->assertIsFloat($payload['satisfaction']);
        $this->assertSame($prize->id, $payload['id']);
        $this->assertSame(42, $payload['psc']);
        $this->assertEqualsWithDelta(3.1415, $payload['satisfaction'], 0.0001);
        $this->assertNotEmpty($payload['created_at']);
        $this->assertNotEmpty($payload['updated_at']);
    }

    public function test_show_nonexistent_level_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson($this->prizePath(999999))->assertNotFound();
    }

    public function test_show_does_not_leak_prize_from_another_level(): void
    {
        $this->actingAsSuperAdmin();

        $levelA = Level::factory()->create();
        $levelB = Level::factory()->create();

        LevelPrize::factory()->create([
            'level_id' => $levelA->id,
            'psc' => 111,
        ]);
        LevelPrize::factory()->create([
            'level_id' => $levelB->id,
            'psc' => 222,
        ]);

        $this->getJson($this->prizePath($levelA))
            ->assertOk()
            ->assertJsonPath('data.prize.psc', 111);

        $this->getJson($this->prizePath($levelB))
            ->assertOk()
            ->assertJsonPath('data.prize.psc', 222);
    }

    // -------------------------------------------------------------------------
    // Store — happy path
    // -------------------------------------------------------------------------

    public function test_store_creates_prize_and_persists_validated_fields(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $payload = $this->validPayload([
            'psc' => 500,
            'yellow' => 10,
            'blue' => 20,
            'red' => 30,
            'effect' => 40,
            'satisfaction' => 12.3456,
        ]);

        $response = $this->postJson($this->prizePath($level), $payload)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.prize.psc', 500)
            ->assertJsonPath('data.prize.yellow', 10)
            ->assertJsonPath('data.prize.blue', 20)
            ->assertJsonPath('data.prize.red', 30)
            ->assertJsonPath('data.prize.effect', 40);

        $this->assertEqualsWithDelta(
            12.3456,
            (float) $response->json('data.prize.satisfaction'),
            0.0001
        );

        $this->assertDatabaseHas('level_prizes', [
            'level_id' => $level->id,
            'psc' => 500,
            'yellow' => 10,
            'blue' => 20,
            'red' => 30,
            'effect' => 40,
        ]);

        $this->assertSame(1, LevelPrize::where('level_id', $level->id)->count());
    }

    public function test_store_allows_zero_boundary_values(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->postJson($this->prizePath($level), $this->validPayload([
            'psc' => 0,
            'yellow' => 0,
            'blue' => 0,
            'red' => 0,
            'effect' => 0,
            'satisfaction' => 0,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.prize.psc', 0)
            ->assertJsonPath('data.prize.satisfaction', 0);

        $this->assertDatabaseHas('level_prizes', [
            'level_id' => $level->id,
            'psc' => 0,
            'satisfaction' => 0,
        ]);
    }

    public function test_store_accepts_integer_string_coercion_for_numeric_fields(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->postJson($this->prizePath($level), [
            'psc' => '15',
            'yellow' => '1',
            'blue' => '2',
            'red' => '3',
            'effect' => '4',
            'satisfaction' => '1.5',
        ])
            ->assertCreated()
            ->assertJsonPath('data.prize.psc', 15)
            ->assertJsonPath('data.prize.yellow', 1)
            ->assertJsonPath('data.prize.satisfaction', 1.5);
    }

    public function test_store_rejects_when_prize_already_exists(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelPrize::factory()->create([
            'level_id' => $level->id,
            'psc' => 77,
        ]);

        $this->postJson($this->prizePath($level), $this->validPayload([
            'psc' => 88,
        ]))
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::STORE_DUPLICATE_MESSAGE)
            ->assertJsonMissingPath('data');

        $this->assertDatabaseHas('level_prizes', [
            'level_id' => $level->id,
            'psc' => 77,
        ]);
        $this->assertSame(1, LevelPrize::where('level_id', $level->id)->count());
    }

    public function test_store_nonexistent_level_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson($this->prizePath(999999), $this->validPayload())
            ->assertNotFound();
    }

    public function test_store_returns_server_error_when_transaction_fails(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        DB::partialMock()
            ->shouldReceive('transaction')
            ->once()
            ->andThrow(new \RuntimeException('forced store failure'));

        $this->postJson($this->prizePath($level), $this->validPayload())
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::STORE_ERROR_MESSAGE);

        $this->assertSame(0, LevelPrize::where('level_id', $level->id)->count());
    }

    // -------------------------------------------------------------------------
    // Store — validation
    // -------------------------------------------------------------------------

    public function test_store_requires_all_fields(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->postJson($this->prizePath($level), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'psc',
                'yellow',
                'blue',
                'red',
                'effect',
                'satisfaction',
            ]);
    }

    #[DataProvider('requiredIntegerFieldProvider')]
    public function test_store_rejects_missing_integer_field(string $field): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $payload = $this->validPayload();
        unset($payload[$field]);

        $this->postJson($this->prizePath($level), $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors([$field]);
    }

    #[DataProvider('requiredIntegerFieldProvider')]
    public function test_store_rejects_non_integer_values(string $field): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->postJson($this->prizePath($level), $this->validPayload([
            $field => 'not-an-integer',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([$field]);
    }

    #[DataProvider('requiredIntegerFieldProvider')]
    public function test_store_rejects_negative_integer_values(string $field): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->postJson($this->prizePath($level), $this->validPayload([
            $field => -1,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([$field]);
    }

    public function test_store_rejects_missing_satisfaction(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $payload = $this->validPayload();
        unset($payload['satisfaction']);

        $this->postJson($this->prizePath($level), $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['satisfaction']);
    }

    public function test_store_rejects_negative_satisfaction(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->postJson($this->prizePath($level), $this->validPayload([
            'satisfaction' => -0.0001,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['satisfaction']);
    }

    public function test_store_rejects_satisfaction_with_too_many_decimal_places(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->postJson($this->prizePath($level), $this->validPayload([
            'satisfaction' => 1.12345,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['satisfaction']);
    }

    public function test_store_rejects_non_numeric_satisfaction(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->postJson($this->prizePath($level), $this->validPayload([
            'satisfaction' => 'abc',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['satisfaction']);
    }

    public function test_store_rejects_float_for_integer_fields(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->postJson($this->prizePath($level), $this->validPayload([
            'psc' => 1.5,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['psc']);
    }

    public function test_store_ignores_unknown_fields_and_does_not_mass_assign_id(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $otherLevel = Level::factory()->create();

        $response = $this->postJson($this->prizePath($level), $this->validPayload([
            'id' => 999999,
            'level_id' => $otherLevel->id,
            'admin_note' => 'should be ignored',
            'is_admin' => true,
        ]))
            ->assertCreated();

        $createdId = $response->json('data.prize.id');

        $this->assertNotSame(999999, $createdId);
        $this->assertDatabaseHas('level_prizes', [
            'id' => $createdId,
            'level_id' => $level->id,
        ]);
        $this->assertDatabaseMissing('level_prizes', [
            'level_id' => $otherLevel->id,
        ]);
        $this->assertArrayNotHasKey('admin_note', $response->json('data.prize'));
        $this->assertArrayNotHasKey('is_admin', $response->json('data.prize'));
    }

    // -------------------------------------------------------------------------
    // Update — happy path
    // -------------------------------------------------------------------------

    public function test_update_modifies_existing_prize(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $prize = LevelPrize::factory()->create([
            'level_id' => $level->id,
            'psc' => 1,
            'yellow' => 1,
            'blue' => 1,
            'red' => 1,
            'effect' => 1,
            'satisfaction' => 1.0,
        ]);

        $this->putJson($this->prizePath($level), $this->validPayload([
            'psc' => 900,
            'yellow' => 8,
            'blue' => 7,
            'red' => 6,
            'effect' => 5,
            'satisfaction' => 9.8765,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.prize.id', $prize->id)
            ->assertJsonPath('data.prize.psc', 900)
            ->assertJsonPath('data.prize.yellow', 8)
            ->assertJsonPath('data.prize.blue', 7)
            ->assertJsonPath('data.prize.red', 6)
            ->assertJsonPath('data.prize.effect', 5);

        $this->assertDatabaseHas('level_prizes', [
            'id' => $prize->id,
            'level_id' => $level->id,
            'psc' => 900,
            'yellow' => 8,
            'blue' => 7,
            'red' => 6,
            'effect' => 5,
        ]);
    }

    public function test_update_returns_not_found_when_prize_missing(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->putJson($this->prizePath($level), $this->validPayload())
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::UPDATE_MISSING_MESSAGE);
    }

    public function test_update_nonexistent_level_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->prizePath(999999), $this->validPayload())
            ->assertNotFound();
    }

    public function test_update_does_not_affect_other_level_prizes(): void
    {
        $this->actingAsSuperAdmin();

        $targetLevel = Level::factory()->create();
        $otherLevel = Level::factory()->create();

        $targetPrize = LevelPrize::factory()->create([
            'level_id' => $targetLevel->id,
            'psc' => 10,
        ]);
        $otherPrize = LevelPrize::factory()->create([
            'level_id' => $otherLevel->id,
            'psc' => 20,
        ]);

        $this->putJson($this->prizePath($targetLevel), $this->validPayload([
            'psc' => 555,
        ]))->assertOk();

        $this->assertDatabaseHas('level_prizes', [
            'id' => $targetPrize->id,
            'psc' => 555,
        ]);
        $this->assertDatabaseHas('level_prizes', [
            'id' => $otherPrize->id,
            'psc' => 20,
        ]);
    }

    public function test_update_ignores_unknown_fields_and_level_id_override(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $otherLevel = Level::factory()->create();
        $prize = LevelPrize::factory()->create([
            'level_id' => $level->id,
            'psc' => 3,
        ]);

        $response = $this->putJson($this->prizePath($level), $this->validPayload([
            'psc' => 44,
            'id' => 123456,
            'level_id' => $otherLevel->id,
            'hacked' => true,
        ]))
            ->assertOk()
            ->assertJsonPath('data.prize.id', $prize->id)
            ->assertJsonPath('data.prize.psc', 44);

        $this->assertDatabaseHas('level_prizes', [
            'id' => $prize->id,
            'level_id' => $level->id,
            'psc' => 44,
        ]);
        $this->assertArrayNotHasKey('hacked', $response->json('data.prize'));
    }

    public function test_update_returns_server_error_when_transaction_fails(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelPrize::factory()->create([
            'level_id' => $level->id,
            'psc' => 11,
        ]);

        DB::partialMock()
            ->shouldReceive('transaction')
            ->once()
            ->andThrow(new \RuntimeException('forced update failure'));

        $this->putJson($this->prizePath($level), $this->validPayload([
            'psc' => 99,
        ]))
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::UPDATE_ERROR_MESSAGE);

        $this->assertSame(11, (int) LevelPrize::where('level_id', $level->id)->value('psc'));
    }

    // -------------------------------------------------------------------------
    // Update — validation
    // -------------------------------------------------------------------------

    public function test_update_requires_all_fields(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelPrize::factory()->create(['level_id' => $level->id]);

        $this->putJson($this->prizePath($level), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'psc',
                'yellow',
                'blue',
                'red',
                'effect',
                'satisfaction',
            ]);
    }

    #[DataProvider('requiredIntegerFieldProvider')]
    public function test_update_rejects_negative_integer_values(string $field): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelPrize::factory()->create(['level_id' => $level->id]);

        $this->putJson($this->prizePath($level), $this->validPayload([
            $field => -5,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([$field]);
    }

    public function test_update_rejects_satisfaction_with_too_many_decimal_places(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelPrize::factory()->create(['level_id' => $level->id]);

        $this->putJson($this->prizePath($level), $this->validPayload([
            'satisfaction' => 0.12345,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['satisfaction']);
    }

    public function test_update_rejects_negative_satisfaction(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelPrize::factory()->create(['level_id' => $level->id]);

        $this->putJson($this->prizePath($level), $this->validPayload([
            'satisfaction' => -1,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['satisfaction']);
    }

    public function test_update_allows_zero_boundary_values(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelPrize::factory()->create([
            'level_id' => $level->id,
            'psc' => 50,
        ]);

        $this->putJson($this->prizePath($level), $this->validPayload([
            'psc' => 0,
            'yellow' => 0,
            'blue' => 0,
            'red' => 0,
            'effect' => 0,
            'satisfaction' => 0,
        ]))
            ->assertOk()
            ->assertJsonPath('data.prize.psc', 0)
            ->assertJsonPath('data.prize.satisfaction', 0);
    }

    // -------------------------------------------------------------------------
    // HTTP method / security surface
    // -------------------------------------------------------------------------

    public function test_delete_method_is_not_allowed_on_prize_endpoint(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelPrize::factory()->create(['level_id' => $level->id]);

        $this->deleteJson($this->prizePath($level))->assertMethodNotAllowed();
    }

    public function test_patch_method_is_not_allowed_on_prize_endpoint(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        LevelPrize::factory()->create(['level_id' => $level->id]);

        $this->patchJson($this->prizePath($level), $this->validPayload())
            ->assertMethodNotAllowed();
    }

    // -------------------------------------------------------------------------
    // Data providers & helpers
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{0: string}>
     */
    public static function requiredIntegerFieldProvider(): array
    {
        return [
            'psc' => ['psc'],
            'yellow' => ['yellow'],
            'blue' => ['blue'],
            'red' => ['red'],
            'effect' => ['effect'],
        ];
    }

    private function prizePath(int|Level $level): string
    {
        $id = $level instanceof Level ? $level->id : $level;

        return '/api/levels/'.$id.'/prize';
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'psc' => 100,
            'yellow' => 10,
            'blue' => 20,
            'red' => 30,
            'effect' => 40,
            'satisfaction' => 1.5,
        ], $overrides);
    }
}
