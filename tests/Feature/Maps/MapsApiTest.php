<?php

namespace Tests\Feature\Maps;

use App\Jobs\ImportMaps;
use App\Models\Map;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesMapsApiSchema;
use Tests\TestCase;

class MapsApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesMapsApiSchema;

    private const INDEX_PATH = '/api/maps';

    private const INDEX_SUCCESS_MESSAGE = 'Maps retrieved successfully.';

    private const STORE_SUCCESS_MESSAGE = 'فایل با موفقیت بارگذاری شد';

    private const UPDATE_SUCCESS_MESSAGE = 'اطلاعات با موفقیت ویرایش شد';

    private const DESTROY_SUCCESS_MESSAGE = 'نقشه با موفقیت حذف شد';

    private const INSERT_SUCCESS_MESSAGE = 'اطلاعات با موفقیت وارد دیتابیس شد';

    private const STORE_ERROR_PREFIX = 'خطا در بارگذاری فایل:';

    private const UPDATE_ERROR_PREFIX = 'خطا در ویرایش اطلاعات:';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpMapsApiSchema();
    }

    protected function tearDown(): void
    {
        $this->tearDownMapsApiSchema();

        parent::tearDown();
    }

    private function mapPath(int|Map $map): string
    {
        $id = $map instanceof Map ? $map->id : $map;

        return self::INDEX_PATH.'/'.$id;
    }

    private function insertPath(int|Map $map): string
    {
        $id = $map instanceof Map ? $map->id : $map;

        return self::INDEX_PATH.'/'.$id.'/insert-into-database';
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
        $this->post(self::INDEX_PATH, $this->validMapStorePayload(), ['Accept' => 'application/json'])
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $map = $this->createMap();

        $this->post($this->mapPath($map), array_merge(
            $this->validMapUpdatePayload(),
            ['_method' => 'PUT']
        ), ['Accept' => 'application/json'])->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $map = $this->createMap();

        $this->deleteJson($this->mapPath($map))->assertUnauthorized();
    }

    public function test_unauthenticated_insert_into_database_returns_unauthorized(): void
    {
        $map = $this->createMap();

        $this->postJson($this->insertPath($map))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_index(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_index(): void
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

    public function test_index_returns_empty_collection_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data.maps', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_returns_full_json_structure(): void
    {
        $this->actingAsSuperAdmin();

        $this->createMap(['name' => 'Structure Map']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'maps' => [
                        [
                            'id',
                            'name',
                            'publish_date',
                            'publisher_name',
                            'polygon_count',
                            'total_area',
                            'first_id',
                            'last_id',
                            'status',
                            'karbari',
                            'fileName',
                            'central_point_coordinates',
                            'border_coordinates',
                            'polygon_area',
                            'polygon_address',
                            'polygon_color',
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
    }

    public function test_index_orders_by_id_desc(): void
    {
        $this->actingAsSuperAdmin();

        $first = $this->createMap(['name' => 'Older Map']);
        $second = $this->createMap(['name' => 'Newer Map']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.maps.0.id', $second->id)
            ->assertJsonPath('data.maps.1.id', $first->id);
    }

    public function test_index_paginates_with_per_page_and_page_query_params(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 15; $i++) {
            $this->createMap(['name' => "Map {$i}"]);
        }

        $page1 = $this->getJson(self::INDEX_PATH.'?per_page=10&page=1')
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 15)
            ->assertJsonPath('data.pagination.from', 1)
            ->assertJsonPath('data.pagination.to', 10);

        $this->assertCount(10, $page1->json('data.maps'));

        $page2 = $this->getJson(self::INDEX_PATH.'?per_page=10&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 15)
            ->assertJsonPath('data.pagination.from', 11)
            ->assertJsonPath('data.pagination.to', 15);

        $this->assertCount(5, $page2->json('data.maps'));
    }

    public function test_index_defaults_to_per_page_10_and_page_1(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 12; $i++) {
            $this->createMap(['name' => "Default page map {$i}"]);
        }

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonCount(10, 'data.maps');
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_requires_all_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'map_file', 'point_file', 'border_file', 'color']);
    }

    public function test_store_rejects_name_shorter_than_two_characters(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validMapStorePayload([
            'name' => 'A',
        ]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_creates_map_with_computed_fields_and_karbari_mapping(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $suffix = Str::lower(Str::random(8));
        $mapFileName = "map-{$suffix}.txt";

        $this->post(self::INDEX_PATH, $this->validMapStorePayload([
            'name' => 'Tehran District',
            'color' => '#22C55E',
            'map_file' => $this->fakeMapUploadFile($mapFileName, $this->sampleMapFileContent('m')),
            'border_file' => $this->fakeBorderUploadFile("border-{$suffix}.txt"),
            'point_file' => $this->fakePointUploadFile("point-{$suffix}.txt"),
        ]), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $map = Map::query()->where('fileName', $mapFileName)->first();
        $this->assertNotNull($map);
        $this->assertSame('Tehran District', $map->name);
        $this->assertSame(2, (int) $map->polygon_count);
        $this->assertSame(350, (int) $map->total_area); // (100*2) + (50*3)
        $this->assertSame('A-1', $map->first_id);
        $this->assertSame('A-2', $map->last_id);
        $this->assertSame('مسکونی', $map->karbari);
        $this->assertSame(0, (int) $map->status);
        $this->assertSame($admin->name, $map->publisher_name);
        $this->assertSame(now()->format('Y/m/d'), $map->publish_date);
        $this->assertSame('#22C55E', $map->polygon_color);
        $this->assertSame($mapFileName, $map->fileName);
        $this->assertSame(999, (int) $map->polygon_area);
        $this->assertSame(json_encode('Tehran'), $map->polygon_address);
        $this->assertSame(json_encode([[10, 20], [30, 40], [10, 20]]), $map->border_coordinates);
        $this->assertSame(json_encode([51.3, 35.7]), $map->central_point_coordinates);
        $this->assertFileExists(public_path('uploads/maps/'.$mapFileName));
    }

    public function test_store_keeps_unknown_karbari_as_raw_value(): void
    {
        $this->actingAsSuperAdmin();

        $suffix = Str::lower(Str::random(8));
        $mapFileName = "map-unknown-{$suffix}.txt";

        $this->post(self::INDEX_PATH, $this->validMapStorePayload([
            'map_file' => $this->fakeMapUploadFile($mapFileName, $this->sampleMapFileContent('xyz')),
            'border_file' => $this->fakeBorderUploadFile("border-unknown-{$suffix}.txt"),
            'point_file' => $this->fakePointUploadFile("point-unknown-{$suffix}.txt"),
        ]), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $map = Map::query()->where('fileName', $mapFileName)->first();
        $this->assertNotNull($map);
        $this->assertSame('xyz', $map->karbari);
    }

    public function test_store_returns_500_for_malformed_file_content(): void
    {
        $this->actingAsSuperAdmin();

        $suffix = Str::lower(Str::random(8));

        $response = $this->post(self::INDEX_PATH, $this->validMapStorePayload([
            'map_file' => $this->fakeMapUploadFile("bad-map-{$suffix}.txt", 'not-valid-prefix-json'),
            'border_file' => $this->fakeBorderUploadFile("bad-border-{$suffix}.txt"),
            'point_file' => $this->fakePointUploadFile("bad-point-{$suffix}.txt"),
        ]), ['Accept' => 'application/json']);

        $response->assertStatus(500)
            ->assertJsonPath('success', false);

        $this->assertStringStartsWith(self::STORE_ERROR_PREFIX, (string) $response->json('message'));
    }

    public function test_store_writes_uploaded_files_under_uploads_maps(): void
    {
        $this->actingAsSuperAdmin();

        $suffix = Str::lower(Str::random(8));
        $mapName = "persist-map-{$suffix}.txt";
        $borderName = "persist-border-{$suffix}.txt";
        $pointName = "persist-point-{$suffix}.txt";

        $this->post(self::INDEX_PATH, $this->validMapStorePayload([
            'map_file' => $this->fakeMapUploadFile($mapName),
            'border_file' => $this->fakeBorderUploadFile($borderName),
            'point_file' => $this->fakePointUploadFile($pointName),
        ]), ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertFileExists(public_path('uploads/maps/'.$mapName));
        $this->assertFileExists(public_path('uploads/maps/'.$borderName));
        $this->assertFileExists(public_path('uploads/maps/'.$pointName));
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_returns_not_found_for_missing_map(): void
    {
        $this->actingAsSuperAdmin();

        $this->post($this->mapPath(999999), array_merge(
            $this->validMapUpdatePayload(),
            ['_method' => 'PUT']
        ), ['Accept' => 'application/json'])->assertNotFound();
    }

    public function test_update_requires_all_fields(): void
    {
        $this->actingAsSuperAdmin();

        $map = $this->createMap();

        $this->post($this->mapPath($map), [
            '_method' => 'PUT',
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'point_file', 'border_file', 'color']);
    }

    public function test_update_changes_name_color_coordinates_area_and_address(): void
    {
        $this->actingAsSuperAdmin();

        $map = $this->createMap([
            'name' => 'Before',
            'polygon_color' => '#000000',
            'polygon_area' => 1,
            'polygon_address' => json_encode('Old'),
        ]);

        $this->post($this->mapPath($map), array_merge(
            $this->validMapUpdatePayload([
                'name' => 'After Edit',
                'color' => '#EF4444',
            ]),
            ['_method' => 'PUT']
        ), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $map->refresh();

        $this->assertSame('After Edit', $map->name);
        $this->assertSame('#EF4444', $map->polygon_color);
        $this->assertSame(777, (int) $map->polygon_area);
        $this->assertSame(json_encode('Isfahan'), $map->polygon_address);
        $this->assertSame(json_encode([[11, 22], [33, 44], [11, 22]]), $map->border_coordinates);
        $this->assertSame(json_encode([52.1, 36.2]), $map->central_point_coordinates);
    }

    public function test_update_returns_500_for_malformed_files(): void
    {
        $this->actingAsSuperAdmin();

        $map = $this->createMap();
        $suffix = Str::lower(Str::random(8));

        $response = $this->post($this->mapPath($map), [
            '_method' => 'PUT',
            'name' => 'Broken Update',
            'color' => '#111111',
            'border_file' => $this->fakeBorderUploadFile("bad-upd-border-{$suffix}.txt", 'no-equals-json'),
            'point_file' => $this->fakePointUploadFile("bad-upd-point-{$suffix}.txt"),
        ], ['Accept' => 'application/json']);

        $response->assertStatus(500)
            ->assertJsonPath('success', false);

        $this->assertStringStartsWith(self::UPDATE_ERROR_PREFIX, (string) $response->json('message'));
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_returns_not_found_for_missing_map(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson($this->mapPath(999999))->assertNotFound();
    }

    public function test_destroy_deletes_database_row_and_file_when_present(): void
    {
        $this->actingAsSuperAdmin();

        $fileName = 'delete-me-'.Str::uuid().'.txt';
        $this->putMapUploadFile($fileName, 'map-data');

        $map = $this->createMap(['fileName' => $fileName]);

        $this->assertFileExists(public_path('uploads/maps/'.$fileName));

        $this->deleteJson($this->mapPath($map))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('maps', ['id' => $map->id]);
        $this->assertFileDoesNotExist(public_path('uploads/maps/'.$fileName));
    }

    public function test_destroy_succeeds_when_file_already_missing(): void
    {
        $this->actingAsSuperAdmin();

        $map = $this->createMap([
            'fileName' => 'missing-file-'.Str::uuid().'.txt',
        ]);

        $this->assertFileDoesNotExist(public_path('uploads/maps/'.$map->fileName));

        $this->deleteJson($this->mapPath($map))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('maps', ['id' => $map->id]);
    }

    // -------------------------------------------------------------------------
    // InsertIntoDatabase
    // -------------------------------------------------------------------------

    public function test_insert_into_database_dispatches_import_maps_job_and_sets_status(): void
    {
        $this->actingAsSuperAdmin();
        Queue::fake();

        $map = $this->createMap(['status' => 0]);

        $this->postJson($this->insertPath($map))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INSERT_SUCCESS_MESSAGE);

        $map->refresh();
        $this->assertSame(1, (int) $map->status);

        Queue::assertPushed(ImportMaps::class);
    }

    public function test_insert_into_database_with_bus_fake_dispatches_import_maps(): void
    {
        $this->actingAsSuperAdmin();
        Bus::fake();

        $map = $this->createMap(['status' => 0]);

        $this->postJson($this->insertPath($map))
            ->assertOk()
            ->assertJsonPath('success', true);

        $map->refresh();
        $this->assertSame(1, (int) $map->status);

        Bus::assertDispatched(ImportMaps::class);
    }

    public function test_insert_into_database_returns_not_found_for_missing_map(): void
    {
        $this->actingAsSuperAdmin();
        Queue::fake();

        $this->postJson($this->insertPath(999999))->assertNotFound();

        Queue::assertNothingPushed();
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_store_rejects_non_file_uploads(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, [
            'name' => 'Invalid Files',
            'color' => '#7C3AED',
            'map_file' => 'not-a-file',
            'border_file' => 'not-a-file',
            'point_file' => 'not-a-file',
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['map_file', 'border_file', 'point_file']);
    }

    public function test_update_rejects_name_shorter_than_two_characters(): void
    {
        $this->actingAsSuperAdmin();

        $map = $this->createMap();

        $this->post($this->mapPath($map), array_merge(
            $this->validMapUpdatePayload(['name' => 'X']),
            ['_method' => 'PUT']
        ), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function test_store_maps_commercial_karbari_title(): void
    {
        $this->actingAsSuperAdmin();

        $suffix = Str::lower(Str::random(8));
        $mapFileName = "map-commercial-{$suffix}.txt";

        $this->post(self::INDEX_PATH, $this->validMapStorePayload([
            'map_file' => $this->fakeMapUploadFile($mapFileName, $this->sampleMapFileContent('t')),
            'border_file' => $this->fakeBorderUploadFile("border-commercial-{$suffix}.txt"),
            'point_file' => $this->fakePointUploadFile("point-commercial-{$suffix}.txt"),
        ]), ['Accept' => 'application/json'])
            ->assertOk();

        $map = Map::query()->where('fileName', $mapFileName)->first();
        $this->assertNotNull($map);
        $this->assertSame('تجاری', $map->karbari);
    }

    public function test_regular_admin_can_store_update_and_destroy(): void
    {
        $admin = $this->actingAsRegularAdmin();

        $suffix = Str::lower(Str::random(8));
        $mapFileName = "regular-map-{$suffix}.txt";

        $this->post(self::INDEX_PATH, $this->validMapStorePayload([
            'name' => 'Regular Admin Map',
            'map_file' => $this->fakeMapUploadFile($mapFileName),
            'border_file' => $this->fakeBorderUploadFile("regular-border-{$suffix}.txt"),
            'point_file' => $this->fakePointUploadFile("regular-point-{$suffix}.txt"),
        ]), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $map = Map::query()->where('fileName', $mapFileName)->first();
        $this->assertNotNull($map);
        $this->assertSame($admin->name, $map->publisher_name);

        $this->post($this->mapPath($map), array_merge(
            $this->validMapUpdatePayload(['name' => 'Regular Updated']),
            ['_method' => 'PUT']
        ), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->deleteJson($this->mapPath($map))
            ->assertOk()
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
    }

    public function test_store_rejects_oversized_map_file(): void
    {
        $this->actingAsSuperAdmin();

        $oversized = UploadedFile::fake()->create('huge-map.txt', 10241);

        $this->post(self::INDEX_PATH, $this->validMapStorePayload([
            'map_file' => $oversized,
        ]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['map_file']);
    }

    #[DataProvider('karbariTitleProvider')]
    public function test_store_maps_karbari_codes_to_titles(string $code, string $title): void
    {
        $this->actingAsSuperAdmin();

        $suffix = Str::lower(Str::random(8));
        $mapFileName = "map-{$code}-{$suffix}.txt";

        $this->post(self::INDEX_PATH, $this->validMapStorePayload([
            'map_file' => $this->fakeMapUploadFile($mapFileName, $this->sampleMapFileContent($code)),
            'border_file' => $this->fakeBorderUploadFile("border-{$code}-{$suffix}.txt"),
            'point_file' => $this->fakePointUploadFile("point-{$code}-{$suffix}.txt"),
        ]), ['Accept' => 'application/json'])
            ->assertOk();

        $map = Map::query()->where('fileName', $mapFileName)->first();
        $this->assertNotNull($map);
        $this->assertSame($title, $map->karbari);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function karbariTitleProvider(): array
    {
        return [
            'office' => ['e', 'اداری'],
            'education' => ['a', 'آموزشی'],
            'health' => ['b', 'بهداشتی'],
            'green' => ['s', 'فضای سبز'],
            'culture' => ['f', 'فرهنگی'],
            'tourism' => ['g', 'گردشگری'],
            'religious' => ['z', 'مذهبی'],
            'exhibition' => ['n', 'نمایشگاه'],
        ];
    }

    public function test_destroy_returns_500_when_delete_fails(): void
    {
        $this->actingAsSuperAdmin();

        $map = $this->createMap();

        Map::deleting(function () {
            throw new \RuntimeException('forced map delete failure');
        });

        $response = $this->deleteJson($this->mapPath($map))
            ->assertStatus(500)
            ->assertJsonPath('success', false);

        $this->assertStringStartsWith('خطا در حذف نقشه:', (string) $response->json('message'));
    }

    public function test_insert_into_database_returns_500_when_update_fails(): void
    {
        $this->actingAsSuperAdmin();
        Queue::fake();

        $map = $this->createMap(['status' => 0]);

        Map::updating(function () {
            throw new \RuntimeException('forced map status update failure');
        });

        $response = $this->postJson($this->insertPath($map))
            ->assertStatus(500)
            ->assertJsonPath('success', false);

        $this->assertStringStartsWith('خطا در وارد کردن اطلاعات:', (string) $response->json('message'));
    }
}
