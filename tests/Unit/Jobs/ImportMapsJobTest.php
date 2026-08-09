<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ImportMaps;
use App\Models\Coordinate;
use App\Models\Crs;
use App\Models\CrsProperty;
use App\Models\Feature;
use App\Models\FeatureProperties;
use App\Models\Geometry;
use App\Models\Map;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\CreatesMapsApiSchema;
use Tests\TestCase;

class ImportMapsJobTest extends TestCase
{
    use CreatesMapsApiSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpMapsApiSchema();
        $this->createImportMapsRelatedTables();
        $this->seedSystemOwner();
    }

    protected function tearDown(): void
    {
        $this->tearDownMapsApiSchema();
        parent::tearDown();
    }

    public function test_handle_imports_crs_features_properties_geometry_and_coordinates(): void
    {
        $fileName = 'import-job-map.txt';
        $this->putMapUploadFile($fileName, $this->sampleMapFileContent('m'));

        $map = $this->createMap(['fileName' => $fileName]);

        (new ImportMaps($map))->handle();

        $this->assertDatabaseCount('crs', 1);
        $this->assertDatabaseHas('crs', [
            'type' => 'name',
            'map_id' => $map->id,
        ]);
        $this->assertDatabaseCount('crs_properties', 1);
        $this->assertDatabaseHas('crs_properties', [
            'name' => 'EPSG:4326',
        ]);

        $this->assertSame(2, Feature::query()->where('map_id', $map->id)->count());
        $this->assertDatabaseHas('feature_properties', [
            'id' => 'A-1',
            'id_prefix' => 'A',
            'id_postfix' => 1,
            'karbari' => 'm',
            'stability' => 200,
        ]);
        $this->assertDatabaseHas('feature_properties', [
            'id' => 'A-2',
            'stability' => 150,
        ]);

        $this->assertSame(2, Geometry::query()->count());
        $this->assertGreaterThanOrEqual(4, Coordinate::query()->count());
        $this->assertDatabaseHas('coordinates', ['x' => 1, 'y' => 2]);
        $this->assertDatabaseHas('coordinates', ['x' => 5, 'y' => 6]);
    }

    public function test_handle_does_nothing_when_decoded_payload_is_empty(): void
    {
        $fileName = 'empty-import-map.txt';
        $this->putMapUploadFile($fileName, 'data=null');

        $map = $this->createMap(['fileName' => $fileName]);

        (new ImportMaps($map))->handle();

        $this->assertSame(0, Crs::query()->count());
        $this->assertSame(0, Feature::query()->count());
        $this->assertSame(0, FeatureProperties::query()->count());
    }

    private function createImportMapsRelatedTables(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('code')->nullable();
                $table->string('password')->nullable();
                $table->string('ip')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('crs')) {
            Schema::create('crs', function (Blueprint $table) {
                $table->id();
                $table->string('type')->nullable();
                $table->unsignedBigInteger('map_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('crs_properties')) {
            Schema::create('crs_properties', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->unsignedBigInteger('crs_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('features')) {
            Schema::create('features', function (Blueprint $table) {
                $table->id();
                $table->string('type')->nullable();
                $table->unsignedBigInteger('map_id')->nullable();
                $table->unsignedBigInteger('owner_id')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('feature_properties')) {
            Schema::create('feature_properties', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('id_prefix')->nullable();
                $table->unsignedBigInteger('id_postfix')->nullable();
                $table->unsignedBigInteger('feature_id');
                $table->string('address')->nullable();
                $table->integer('density')->nullable();
                $table->string('date')->nullable();
                $table->decimal('stability', 16, 4)->nullable();
                $table->string('label')->nullable();
                $table->decimal('area', 16, 4)->nullable();
                $table->string('region')->nullable();
                $table->string('karbari')->nullable();
                $table->string('owner')->nullable();
                $table->string('rgb')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('geometries')) {
            Schema::create('geometries', function (Blueprint $table) {
                $table->id();
                $table->string('type')->nullable();
                $table->unsignedBigInteger('feature_id');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('coordinates')) {
            Schema::create('coordinates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('geometry_id');
                $table->decimal('x', 16, 8)->nullable();
                $table->decimal('y', 16, 8)->nullable();
                $table->timestamps();
            });
        }
    }

    private function seedSystemOwner(): void
    {
        if (! \App\Models\User::query()->whereKey(1)->exists()) {
            \App\Models\User::query()->create([
                'id' => 1,
                'name' => 'System',
                'email' => 'system@import.test',
                'code' => 'SYS',
                'password' => 'secret',
                'ip' => '127.0.0.1',
            ]);
        }
    }
}
