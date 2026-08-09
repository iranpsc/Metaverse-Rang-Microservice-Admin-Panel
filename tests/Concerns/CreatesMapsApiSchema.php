<?php

namespace Tests\Concerns;

use App\Models\Map;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait CreatesMapsApiSchema
{
    use CreatesCitizensApiSchema;

    /** @var list<string> */
    private array $mapsUploadFilesCreated = [];

    protected function setUpMapsApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createMapsTable();

        // Real public/uploads is often a broken symlink in local/dev. Use a writable
        // testing public path so storePubliclyAs + file_get_contents(public_path(...)) agree.
        // Do NOT use Storage::fake('public') — it breaks file_get_contents(public_path(...)).
        $testingPublic = storage_path('framework/testing/maps-public');
        File::ensureDirectoryExists($testingPublic.'/uploads/maps');
        $this->app->usePublicPath($testingPublic);

        config(['filesystems.disks.public.root' => public_path('uploads')]);
    }

    protected function tearDownMapsApiSchema(): void
    {
        foreach ($this->mapsUploadFilesCreated as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $this->mapsUploadFilesCreated = [];

        $mapsDir = storage_path('framework/testing/maps-public/uploads/maps');
        if (is_dir($mapsDir)) {
            foreach (glob($mapsDir.'/*') ?: [] as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    private function createMapsTable(): void
    {
        if (Schema::hasTable('maps')) {
            return;
        }

        Schema::create('maps', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('publish_date')->nullable();
            $table->string('publisher_name')->nullable();
            $table->bigInteger('polygon_count')->nullable();
            $table->bigInteger('total_area')->nullable();
            $table->string('first_id')->nullable();
            $table->string('last_id')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->string('karbari')->nullable();
            $table->string('fileName')->nullable();
            $table->longText('central_point_coordinates')->nullable();
            $table->longText('border_coordinates')->nullable();
            $table->unsignedBigInteger('polygon_area')->default(0);
            $table->text('polygon_address')->nullable();
            $table->string('polygon_color')->nullable();
        });
    }

    protected function createMap(array $overrides = []): Map
    {
        return Map::create(array_merge([
            'name' => 'Test Map '.Str::random(5),
            'publish_date' => now()->format('Y/m/d'),
            'publisher_name' => 'Test Publisher',
            'polygon_count' => 2,
            'total_area' => 350,
            'first_id' => 'A-1',
            'last_id' => 'A-2',
            'status' => 0,
            'karbari' => 'مسکونی',
            'fileName' => 'map-'.Str::uuid().'.txt',
            'central_point_coordinates' => json_encode([51.3, 35.7]),
            'border_coordinates' => json_encode([[10, 20], [30, 40], [10, 20]]),
            'polygon_area' => 999,
            'polygon_address' => json_encode('Tehran'),
            'polygon_color' => '#7C3AED',
        ], $overrides));
    }

    /**
     * Write a file under public/uploads/maps and track it for tearDown cleanup.
     */
    protected function putMapUploadFile(string $fileName, string $contents = 'placeholder'): string
    {
        $path = public_path('uploads/maps/'.$fileName);
        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, $contents);
        $this->mapsUploadFilesCreated[] = $path;

        return $path;
    }

    protected function trackMapsUploadFile(string $fileName): void
    {
        $this->mapsUploadFilesCreated[] = public_path('uploads/maps/'.$fileName);
    }

    protected function sampleMapFileContent(string $karbari = 'm'): string
    {
        $payload = [
            'type' => 'FeatureCollection',
            'crs' => [
                'type' => 'name',
                'properties' => ['name' => 'EPSG:4326'],
            ],
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => 'A-1',
                        'area' => 100,
                        'density' => 2,
                        'karbari' => $karbari,
                        'address' => 'addr',
                        'date' => '2024',
                        'label' => 'L',
                        'Region' => 'R',
                        'owner' => 'O',
                        'rgb' => '1,2,3',
                    ],
                    'geometry' => [
                        'type' => 'MultiPolygon',
                        'coordinates' => [[[[1, 2], [3, 4], [1, 2]]]],
                    ],
                ],
                [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => 'A-2',
                        'area' => 50,
                        'density' => 3,
                        'karbari' => $karbari,
                        'address' => '',
                        'date' => '2024',
                        'label' => '',
                        'Region' => 'R',
                        'owner' => 'O',
                        'rgb' => '1,2,3',
                    ],
                    'geometry' => [
                        'type' => 'MultiPolygon',
                        'coordinates' => [[[[5, 6], [7, 8], [5, 6]]]],
                    ],
                ],
            ],
        ];

        return 'data='.json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    protected function sampleBorderFileContent(
        int $area = 999,
        string $address = 'Tehran',
        array $ring = [[10, 20], [30, 40], [10, 20]]
    ): string {
        $payload = [
            'features' => [
                [
                    'geometry' => [
                        'type' => 'MultiPolygon',
                        'coordinates' => [[$ring]],
                    ],
                    'properties' => [
                        'area' => $area,
                        'address' => $address,
                    ],
                ],
            ],
        ];

        return 'data='.json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    protected function samplePointFileContent(array $coordinates = [51.3, 35.7]): string
    {
        $payload = [
            'features' => [
                [
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => $coordinates,
                    ],
                ],
            ],
        ];

        return 'data='.json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    protected function fakeMapUploadFile(string $name = 'map.txt', ?string $content = null): UploadedFile
    {
        $content ??= $this->sampleMapFileContent();
        $this->trackMapsUploadFile($name);

        return UploadedFile::fake()->createWithContent($name, $content);
    }

    protected function fakeBorderUploadFile(string $name = 'border.txt', ?string $content = null): UploadedFile
    {
        $content ??= $this->sampleBorderFileContent();
        $this->trackMapsUploadFile($name);

        return UploadedFile::fake()->createWithContent($name, $content);
    }

    protected function fakePointUploadFile(string $name = 'point.txt', ?string $content = null): UploadedFile
    {
        $content ??= $this->samplePointFileContent();
        $this->trackMapsUploadFile($name);

        return UploadedFile::fake()->createWithContent($name, $content);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validMapStorePayload(array $overrides = []): array
    {
        $suffix = Str::lower(Str::random(8));

        return array_merge([
            'name' => 'Sample Map',
            'color' => '#7C3AED',
            'map_file' => $this->fakeMapUploadFile("map-{$suffix}.txt"),
            'border_file' => $this->fakeBorderUploadFile("border-{$suffix}.txt"),
            'point_file' => $this->fakePointUploadFile("point-{$suffix}.txt"),
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validMapUpdatePayload(array $overrides = []): array
    {
        $suffix = Str::lower(Str::random(8));

        return array_merge([
            'name' => 'Updated Map',
            'color' => '#06B6D4',
            'border_file' => $this->fakeBorderUploadFile(
                "border-upd-{$suffix}.txt",
                $this->sampleBorderFileContent(777, 'Isfahan', [[11, 22], [33, 44], [11, 22]])
            ),
            'point_file' => $this->fakePointUploadFile(
                "point-upd-{$suffix}.txt",
                $this->samplePointFileContent([52.1, 36.2])
            ),
        ], $overrides);
    }
}
