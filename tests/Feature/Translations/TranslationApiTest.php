<?php

namespace Tests\Feature\Translations;

use App\Models\Admin;
use App\Models\Translations\Translation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class TranslationApiTest extends TestCase
{
    use CreatesAuthApiSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthApiSchema();
        $this->createTranslationsTable();
        $this->createTranslationStructureTables();

        Cache::forget('translations.available_languages');
    }

    public function test_admin_can_fetch_available_languages(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/translations/languages');

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

        $response = $this->postJson('/api/translations', [
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

        $response = $this->patchJson("/api/translations/{$translation->id}/status");

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

    private function createTranslationsTable(): void
    {
        if (Schema::connection('sqlite')->hasTable('translations')) {
            return;
        }

        Schema::connection('sqlite')->create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->string('native_name')->nullable();
            $table->string('direction')->nullable();
            $table->boolean('status')->default(false);
            $table->unsignedTinyInteger('version')->default(0);
            $table->string('file_url')->nullable();
        });
    }

    private function createTranslationStructureTables(): void
    {
        $schema = Schema::connection('sqlite');

        if (! $schema->hasTable('modals')) {
            $schema->create('modals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('translation_id')->constrained()->cascadeOnDelete();
                $table->string('name');
            });
        }

        if (! $schema->hasTable('tabs')) {
            $schema->create('tabs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('modal_id')->constrained()->cascadeOnDelete();
                $table->string('name');
            });
        }

        if (! $schema->hasTable('fields')) {
            $schema->create('fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tab_id')->constrained()->cascadeOnDelete();
                $table->string('unique_id')->nullable();
                $table->text('translation')->nullable();
            });
        }
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
