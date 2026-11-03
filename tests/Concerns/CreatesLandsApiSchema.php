<?php

namespace Tests\Concerns;

use App\Models\Coordinate;
use App\Models\Feature;
use App\Models\FeatureProperties;
use App\Models\Geometry;
use App\Models\Map;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait CreatesLandsApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpLandsApiSchema(): void
    {
        $this->setUpCitizensApiSchema();

        $this->createMapsTable();
        $this->createFeaturesTable();
        $this->createFeaturePropertiesTable();
        $this->createGeometriesTable();
        $this->createCoordinatesTable();
        $this->seedSystemUser();
    }

    private function createMapsTable(): void
    {
        if (Schema::hasTable('maps')) {
            return;
        }

        Schema::create('maps', function (Blueprint $table) {
            $table->id();
            $table->string('fileName')->nullable();
            $table->tinyInteger('status')->default(0);
        });
    }

    private function createFeaturesTable(): void
    {
        if (Schema::hasTable('features')) {
            return;
        }

        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('map_id')->nullable()->default(0);
            $table->string('type')->nullable()->default('land');
            $table->unsignedBigInteger('owner_id')->default(1);
            $table->timestamps();
        });
    }

    private function createFeaturePropertiesTable(): void
    {
        if (Schema::hasTable('feature_properties')) {
            return;
        }

        Schema::create('feature_properties', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('id_prefix')->nullable();
            $table->unsignedBigInteger('id_postfix')->nullable();
            $table->unsignedBigInteger('feature_id');
            $table->decimal('area', 16, 4)->nullable();
            $table->integer('density')->nullable();
            $table->string('karbari')->nullable();
            $table->string('address')->nullable();
            $table->string('rgb')->nullable();
            $table->decimal('price', 16, 4)->nullable();
            $table->timestamps();
        });
    }

    private function createGeometriesTable(): void
    {
        if (Schema::hasTable('geometries')) {
            return;
        }

        Schema::create('geometries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_id');
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    private function createCoordinatesTable(): void
    {
        if (Schema::hasTable('coordinates')) {
            return;
        }

        Schema::create('coordinates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('geometry_id');
            $table->decimal('x', 20, 10)->nullable();
            $table->decimal('y', 20, 10)->nullable();
            $table->string('index')->nullable();
            $table->timestamps();
        });
    }

    protected function seedSystemUser(): User
    {
        $existing = User::query()->find(1);

        if ($existing) {
            return $existing;
        }

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'System',
            'email' => 'system@example.com',
            'code' => 'SYSTEM',
            'password' => 'secret',
            'ip' => '127.0.0.1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::query()->findOrFail(1);
    }

    protected function createMap(array $overrides = []): Map
    {
        return Map::create(array_merge([
            'fileName' => 'test-map.json',
            'status' => 1,
        ], $overrides));
    }

    protected function createCitizenUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Citizen '.Str::random(5),
            'email' => Str::uuid().'@example.com',
            'code' => (string) random_int(1000, 9999),
            'password' => 'secret',
            'ip' => '127.0.0.1',
        ], $overrides));
    }

    protected function createFeature(array $overrides = []): Feature
    {
        $mapId = $overrides['map_id'] ?? $this->createMap()->id;
        unset($overrides['map_id']);

        return Feature::create(array_merge([
            'map_id' => $mapId,
            'type' => 'land',
            'owner_id' => 1,
        ], $overrides));
    }

    protected function createFeatureProperties(Feature $feature, array $overrides = []): FeatureProperties
    {
        $id = $overrides['id'] ?? 'LAND-'.Str::upper(Str::random(6));

        return FeatureProperties::create(array_merge([
            'id' => $id,
            'id_prefix' => explode('-', $id)[0],
            'id_postfix' => random_int(1, 9999),
            'feature_id' => $feature->id,
            'area' => 100,
            'density' => 5,
            'karbari' => 'm',
            'address' => 'Test address',
            'rgb' => '#7C3AED',
            'price' => 1000,
        ], $overrides));
    }

    /**
     * @param  array<int, array{x: float|int, y: float|int}>  $coordinates
     * @return array{geometry: Geometry, coordinates: Collection<int, Coordinate>}
     */
    protected function createGeometryWithCoordinates(Feature $feature, array $coordinates = []): array
    {
        $geometry = Geometry::create([
            'feature_id' => $feature->id,
            'type' => 'Polygon',
        ]);

        if ($coordinates === []) {
            $coordinates = [
                ['x' => 10.5, 'y' => 20.5],
                ['x' => 30.0, 'y' => 40.0],
                ['x' => 50.5, 'y' => 60.5],
            ];
        }

        $created = collect();

        foreach ($coordinates as $coordinate) {
            $created->push(Coordinate::create([
                'geometry_id' => $geometry->id,
                'x' => $coordinate['x'],
                'y' => $coordinate['y'],
            ]));
        }

        return [
            'geometry' => $geometry,
            'coordinates' => $created,
        ];
    }

    protected function createLandWithProperties(array $featureOverrides = [], array $propertyOverrides = []): FeatureProperties
    {
        $feature = $this->createFeature($featureOverrides);

        return $this->createFeatureProperties($feature, $propertyOverrides);
    }
}
