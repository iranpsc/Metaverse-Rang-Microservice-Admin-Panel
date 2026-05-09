<?php

namespace Tests\Feature\Translations;

use App\Models\Admin;
use App\Models\Translations\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TranslationApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        $this->artisan('migrate:fresh', ['--database' => 'sqlite']);
        Cache::forget('translations.available_languages');
    }

    public function test_admin_can_fetch_available_languages(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/v1/translations/languages');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'languages' => [
                        ['code', 'name', 'nativeName', 'dir']
                    ]
                ],
                'message',
            ]);
    }

    public function test_admin_can_create_translation(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/v1/translations', [
            'code' => 'de',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'translation' => [
                        'code' => 'de',
                        'name' => 'German',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('translations', [
            'code' => 'de',
        ], 'sqlite');
    }

    public function test_admin_can_toggle_translation_status(): void
    {
        $this->actingAsAdmin();

        $translation = Translation::create([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
        ]);

        $response = $this->patchJson("/api/v1/translations/{$translation->id}/status");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'translation' => [
                        'id' => $translation->id,
                        'status' => true,
                    ],
                ],
            ]);
    }

    private function actingAsAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => Str::uuid().'@example.com',
            'password' => bcrypt('password'),
        ]);

        Sanctum::actingAs($admin, abilities: ['*'], guard: 'admin');

        return $admin;
    }
}


