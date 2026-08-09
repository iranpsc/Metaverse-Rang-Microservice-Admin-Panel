<?php

namespace Tests\Feature\Lands;

use App\Models\FeatureProperties;
use App\Services\Lands\LandOwnerTransferService;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesLandsApiSchema;
use Tests\TestCase;

class LandsApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesLandsApiSchema;

    private const INDEX_PATH = '/api/lands';

    private const OWNER_TRANSFER_OPTIONS_PATH = '/api/lands/owner-transfer/options';

    private const OWNER_TRANSFER_PATH = '/api/lands/owner-transfer';

    private const INDEX_SUCCESS_MESSAGE = 'Properties retrieved successfully.';

    private const UPDATE_SUCCESS_MESSAGE = 'اطلاعات با موفقیت ثبت شد';

    private const COORDINATE_COUNT_MISMATCH_MESSAGE = 'تعداد مختصات با تعداد موجود همخوانی ندارد';

    private const TRANSFER_SUCCESS_MESSAGE = 'مالکیت زمین با موفقیت منتقل شد';

    private const NON_SYSTEM_OWNED_MESSAGE = 'فقط زمین‌های بدون مالک کاربر قابل انتقال هستند';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLandsApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_owner_transfer_options_returns_unauthorized(): void
    {
        $this->getJson(self::OWNER_TRANSFER_OPTIONS_PATH.'?type=lands')->assertUnauthorized();
    }

    public function test_unauthenticated_transfer_owner_returns_unauthorized(): void
    {
        $this->postJson(self::OWNER_TRANSFER_PATH, [
            'feature_id' => 1,
            'new_owner_id' => 2,
        ])->assertUnauthorized();
    }

    public function test_unauthenticated_update_properties_returns_unauthorized(): void
    {
        $this->putJson($this->propertiesPath(1), $this->validPropertiesPayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_coordinates_returns_unauthorized(): void
    {
        $this->putJson($this->coordinatesPath(1), $this->validCoordinatesPayload())
            ->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $property = $this->createLandWithProperties();
        $this->createGeometryWithCoordinates($property->feature);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->getJson(self::OWNER_TRANSFER_OPTIONS_PATH.'?type=lands')
            ->assertOk()
            ->assertJsonPath('success', true);

        $newOwner = $this->createCitizenUser(['name' => 'Transfer Target']);

        $this->postJson(self::OWNER_TRANSFER_PATH, [
            'feature_id' => $property->feature_id,
            'new_owner_id' => $newOwner->id,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::TRANSFER_SUCCESS_MESSAGE);

        $updatable = $this->createLandWithProperties();

        $this->putJson($this->propertiesPath($updatable->feature_id), $this->validPropertiesPayload())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $coordinateFeature = $this->createFeature();
        $this->createFeatureProperties($coordinateFeature);
        $this->createGeometryWithCoordinates($coordinateFeature);

        $this->putJson($this->coordinatesPath($coordinateFeature->id), $this->validCoordinatesPayload())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $property = $this->createLandWithProperties();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson(self::OWNER_TRANSFER_OPTIONS_PATH.'?type=users')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->putJson($this->propertiesPath($property->feature_id), $this->validPropertiesPayload())
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // -------------------------------------------------------------------------
    // Index — happy path / structure
    // -------------------------------------------------------------------------

    public function test_empty_dataset_returns_empty_collection_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data.properties', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_returns_full_json_structure_with_eager_loaded_relations(): void
    {
        $this->actingAsSuperAdmin();

        $owner = $this->createCitizenUser(['name' => 'Land Owner', 'code' => 'OWNER123']);
        $map = $this->createMap(['fileName' => 'structure-map.json']);
        $feature = $this->createFeature(['map_id' => $map->id, 'owner_id' => $owner->id]);
        $property = $this->createFeatureProperties($feature, ['id' => 'STRUCT-001']);
        $this->createGeometryWithCoordinates($feature, [
            ['x' => 1.1, 'y' => 2.2],
            ['x' => 3.3, 'y' => 4.4],
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'properties' => [
                        [
                            'id',
                            'feature_id',
                            'area',
                            'density',
                            'karbari',
                            'address',
                            'rgb',
                            'feature' => [
                                'id',
                                'map_id',
                                'owner_id',
                                'map',
                                'owner' => ['id', 'name', 'code'],
                                'geometry' => [
                                    'coordinates' => [
                                        ['id', 'x', 'y'],
                                    ],
                                ],
                            ],
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
            ])
            ->assertJsonPath('data.properties.0.id', $property->id)
            ->assertJsonPath('data.properties.0.feature.owner.name', 'Land Owner')
            ->assertJsonPath('data.properties.0.feature.map.fileName', 'structure-map.json')
            ->assertJsonCount(2, 'data.properties.0.feature.geometry.coordinates');
    }

    // -------------------------------------------------------------------------
    // Index — search
    // -------------------------------------------------------------------------

    public function test_search_by_property_id(): void
    {
        $this->actingAsSuperAdmin();

        $this->createLandWithProperties([], ['id' => 'NEEDLE-001']);
        $this->createLandWithProperties([], ['id' => 'OTHER-002']);

        $this->getJson(self::INDEX_PATH.'?search=NEEDLE')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.properties.0.id', 'NEEDLE-001');
    }

    public function test_search_by_owner_code(): void
    {
        $this->actingAsSuperAdmin();

        $owner = $this->createCitizenUser(['code' => 'UNIQUECODE99', 'name' => 'Code Owner']);
        $feature = $this->createFeature(['owner_id' => $owner->id]);
        $matching = $this->createFeatureProperties($feature, ['id' => 'CODE-MATCH-001']);

        $otherFeature = $this->createFeature(['owner_id' => 1]);
        $this->createFeatureProperties($otherFeature, ['id' => 'CODE-MISS-002']);

        $this->getJson(self::INDEX_PATH.'?search=UNIQUECODE99')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.properties.0.id', $matching->id);
    }

    public function test_empty_search_behaves_like_no_filter(): void
    {
        $this->actingAsSuperAdmin();

        $this->createLandWithProperties([], ['id' => 'LAND-A']);
        $this->createLandWithProperties([], ['id' => 'LAND-B']);

        $this->getJson(self::INDEX_PATH.'?search=')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2);
    }

    // -------------------------------------------------------------------------
    // Index — pagination
    // -------------------------------------------------------------------------

    public function test_pagination_respects_per_page_and_page(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 5; $i++) {
            $this->createLandWithProperties([], ['id' => sprintf('PAGE-%03d', $i)]);
        }

        $this->getJson(self::INDEX_PATH.'?'.http_build_query([
            'per_page' => 2,
            'page' => 2,
        ]))
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonCount(2, 'data.properties');
    }

    public function test_properties_are_ordered_by_id_asc(): void
    {
        $this->actingAsSuperAdmin();

        $this->createLandWithProperties([], ['id' => 'ZZZ-003']);
        $this->createLandWithProperties([], ['id' => 'AAA-001']);
        $this->createLandWithProperties([], ['id' => 'MMM-002']);

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $ids = collect($response->json('data.properties'))->pluck('id')->all();

        $this->assertSame(['AAA-001', 'MMM-002', 'ZZZ-003'], $ids);
    }

    // -------------------------------------------------------------------------
    // updateProperties
    // -------------------------------------------------------------------------

    public function test_update_properties_successfully_updates_feature_properties(): void
    {
        $this->actingAsSuperAdmin();

        $property = $this->createLandWithProperties([], [
            'area' => 50,
            'density' => 2,
            'karbari' => 'm',
            'address' => 'Old address',
            'rgb' => '#000000',
        ]);

        $this->putJson($this->propertiesPath($property->feature_id), [
            'area' => 200,
            'density' => 10,
            'karbari' => 't',
            'address' => 'New address',
            'rgb' => '#FF0000',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->assertDatabaseHas('feature_properties', [
            'id' => $property->id,
            'area' => 200,
            'density' => 10,
            'karbari' => 't',
            'address' => 'New address',
            'rgb' => '#FF0000',
        ]);
    }

    public function test_update_properties_returns_not_found_when_feature_has_no_properties(): void
    {
        $this->actingAsSuperAdmin();

        $feature = $this->createFeature();

        $this->putJson($this->propertiesPath($feature->id), $this->validPropertiesPayload())
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Feature properties not found');
    }

    public function test_update_properties_returns_not_found_for_missing_feature(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->propertiesPath(999999), $this->validPropertiesPayload())
            ->assertNotFound();
    }

    public function test_update_properties_validation_requires_all_fields(): void
    {
        $this->actingAsSuperAdmin();

        $property = $this->createLandWithProperties();

        $this->putJson($this->propertiesPath($property->feature_id), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['area', 'density', 'karbari', 'address', 'rgb']);
    }

    public function test_update_properties_validation_rejects_non_numeric_area_and_density(): void
    {
        $this->actingAsSuperAdmin();

        $property = $this->createLandWithProperties();

        $this->putJson($this->propertiesPath($property->feature_id), [
            'area' => 'not-a-number',
            'density' => 'bad',
            'karbari' => 'm',
            'address' => 'Address',
            'rgb' => '#FFFFFF',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['area', 'density']);
    }

    // -------------------------------------------------------------------------
    // updateCoordinates
    // -------------------------------------------------------------------------

    public function test_update_coordinates_successfully_updates_all_coordinates(): void
    {
        $this->actingAsSuperAdmin();

        $feature = $this->createFeature();
        $this->createFeatureProperties($feature);
        $geometryData = $this->createGeometryWithCoordinates($feature, [
            ['x' => 1, 'y' => 2],
            ['x' => 3, 'y' => 4],
        ]);

        $this->putJson($this->coordinatesPath($feature->id), [
            'coordinates' => [
                ['x' => 10.5, 'y' => 20.5],
                ['x' => 30.5, 'y' => 40.5],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->assertDatabaseHas('coordinates', [
            'id' => $geometryData['coordinates'][0]->id,
            'x' => 10.5,
            'y' => 20.5,
        ]);
        $this->assertDatabaseHas('coordinates', [
            'id' => $geometryData['coordinates'][1]->id,
            'x' => 30.5,
            'y' => 40.5,
        ]);
    }

    public function test_update_coordinates_returns_not_found_when_geometry_is_missing(): void
    {
        $this->actingAsSuperAdmin();

        $feature = $this->createFeature();
        $this->createFeatureProperties($feature);

        $this->putJson($this->coordinatesPath($feature->id), $this->validCoordinatesPayload())
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Feature coordinates not found');
    }

    public function test_update_coordinates_returns_unprocessable_when_coordinate_count_mismatch(): void
    {
        $this->actingAsSuperAdmin();

        $feature = $this->createFeature();
        $this->createFeatureProperties($feature);
        $this->createGeometryWithCoordinates($feature, [
            ['x' => 1, 'y' => 2],
            ['x' => 3, 'y' => 4],
        ]);

        $this->putJson($this->coordinatesPath($feature->id), [
            'coordinates' => [
                ['x' => 10, 'y' => 20],
            ],
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::COORDINATE_COUNT_MISMATCH_MESSAGE);
    }

    public function test_update_coordinates_returns_not_found_for_missing_feature(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->coordinatesPath(999999), $this->validCoordinatesPayload())
            ->assertNotFound();
    }

    public function test_update_coordinates_validation_requires_coordinates_array_with_numeric_x_and_y(): void
    {
        $this->actingAsSuperAdmin();

        $feature = $this->createFeature();
        $this->createFeatureProperties($feature);
        $this->createGeometryWithCoordinates($feature);

        $this->putJson($this->coordinatesPath($feature->id), [
            'coordinates' => [
                ['x' => 'bad', 'y' => null],
            ],
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['coordinates.0.x', 'coordinates.0.y']);
    }

    public function test_update_coordinates_validation_requires_coordinates_key(): void
    {
        $this->actingAsSuperAdmin();

        $feature = $this->createFeature();
        $this->createFeatureProperties($feature);
        $this->createGeometryWithCoordinates($feature);

        $this->putJson($this->coordinatesPath($feature->id), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['coordinates']);
    }

    // -------------------------------------------------------------------------
    // ownerTransferOptions
    // -------------------------------------------------------------------------

    public function test_owner_transfer_options_returns_lands_with_disabled_flag_for_non_system_owners(): void
    {
        $this->actingAsSuperAdmin();

        $systemFeature = $this->createFeature(['owner_id' => 1]);
        $systemProperty = $this->createFeatureProperties($systemFeature, ['id' => 'SYS-001']);

        $user = $this->createCitizenUser(['name' => 'Owned Land User']);
        $ownedFeature = $this->createFeature(['owner_id' => $user->id]);
        $ownedProperty = $this->createFeatureProperties($ownedFeature, ['id' => 'OWN-002']);

        $response = $this->getJson(self::OWNER_TRANSFER_OPTIONS_PATH.'?type=lands')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Land options retrieved successfully.')
            ->assertJsonStructure([
                'data' => [
                    'options' => [
                        ['value', 'label', 'disabled'],
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'more',
                    ],
                ],
            ]);

        $options = collect($response->json('data.options'))->keyBy('value');

        $this->assertFalse($options[$systemFeature->id]['disabled']);
        $this->assertSame($systemProperty->id, $options[$systemFeature->id]['label']);
        $this->assertTrue($options[$ownedFeature->id]['disabled']);
        $this->assertStringContainsString('Owned Land User', $options[$ownedFeature->id]['label']);
        $this->assertStringContainsString($ownedProperty->id, $options[$ownedFeature->id]['label']);
    }

    public function test_owner_transfer_options_returns_users_excluding_system_user(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createCitizenUser([
            'name' => 'Eligible User',
            'code' => 'ELIG001',
            'email' => 'eligible@example.com',
        ]);

        $response = $this->getJson(self::OWNER_TRANSFER_OPTIONS_PATH.'?type=users')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'User options retrieved successfully.');

        $values = collect($response->json('data.options'))->pluck('value')->all();

        $this->assertContains($user->id, $values);
        $this->assertNotContains(1, $values);
    }

    public function test_owner_transfer_options_search_filters_lands_by_property_id(): void
    {
        $this->actingAsSuperAdmin();

        $match = $this->createLandWithProperties([], ['id' => 'SEARCH-LAND-001']);
        $this->createLandWithProperties([], ['id' => 'OTHER-LAND-002']);

        $this->getJson(self::OWNER_TRANSFER_OPTIONS_PATH.'?'.http_build_query([
            'type' => 'lands',
            'search' => 'SEARCH-LAND',
        ]))
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.options.0.value', $match->feature_id);
    }

    public function test_owner_transfer_options_search_filters_users_by_name_code_or_email(): void
    {
        $this->actingAsSuperAdmin();

        $byName = $this->createCitizenUser(['name' => 'UniqueNamePerson', 'code' => '1111', 'email' => 'a@example.com']);
        $this->createCitizenUser(['name' => 'Other', 'code' => '2222', 'email' => 'b@example.com']);

        $this->getJson(self::OWNER_TRANSFER_OPTIONS_PATH.'?'.http_build_query([
            'type' => 'users',
            'search' => 'UniqueNamePerson',
        ]))
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.options.0.value', $byName->id);

        $byCode = $this->createCitizenUser(['name' => 'Code Search', 'code' => 'FINDME99', 'email' => 'c@example.com']);

        $this->getJson(self::OWNER_TRANSFER_OPTIONS_PATH.'?'.http_build_query([
            'type' => 'users',
            'search' => 'FINDME99',
        ]))
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.options.0.value', $byCode->id);
    }

    public function test_owner_transfer_options_respects_pagination_parameters(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 5; $i++) {
            $this->createLandWithProperties([], ['id' => sprintf('OPT-%03d', $i)]);
        }

        $this->getJson(self::OWNER_TRANSFER_OPTIONS_PATH.'?'.http_build_query([
            'type' => 'lands',
            'per_page' => 2,
            'page' => 2,
        ]))
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.more', true)
            ->assertJsonCount(2, 'data.options');
    }

    public function test_owner_transfer_options_validation_requires_valid_type(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::OWNER_TRANSFER_OPTIONS_PATH.'?type=invalid')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_owner_transfer_options_validation_rejects_per_page_above_maximum(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::OWNER_TRANSFER_OPTIONS_PATH.'?'.http_build_query([
            'type' => 'lands',
            'per_page' => 101,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_owner_transfer_options_validation_rejects_search_longer_than_255_characters(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::OWNER_TRANSFER_OPTIONS_PATH.'?'.http_build_query([
            'type' => 'lands',
            'search' => str_repeat('a', 256),
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['search']);
    }

    // -------------------------------------------------------------------------
    // transferOwner
    // -------------------------------------------------------------------------

    public function test_transfer_owner_successfully_transfers_system_owned_land(): void
    {
        $this->actingAsSuperAdmin();

        $feature = $this->createFeature(['owner_id' => 1]);
        $this->createFeatureProperties($feature, ['id' => 'TRANSFER-001']);
        $newOwner = $this->createCitizenUser(['name' => 'New Owner']);

        $this->postJson(self::OWNER_TRANSFER_PATH, [
            'feature_id' => $feature->id,
            'new_owner_id' => $newOwner->id,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::TRANSFER_SUCCESS_MESSAGE);

        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'owner_id' => $newOwner->id,
        ]);
    }

    public function test_transfer_owner_returns_unprocessable_for_non_system_owned_land(): void
    {
        $this->actingAsSuperAdmin();

        $owner = $this->createCitizenUser(['name' => 'Current Owner']);
        $feature = $this->createFeature(['owner_id' => $owner->id]);
        $this->createFeatureProperties($feature, ['id' => 'OWNED-001']);
        $newOwner = $this->createCitizenUser(['name' => 'Attempted Owner']);

        $this->postJson(self::OWNER_TRANSFER_PATH, [
            'feature_id' => $feature->id,
            'new_owner_id' => $newOwner->id,
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::NON_SYSTEM_OWNED_MESSAGE);

        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'owner_id' => $owner->id,
        ]);
    }

    public function test_transfer_owner_validation_requires_feature_id_and_new_owner_id(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::OWNER_TRANSFER_PATH, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['feature_id', 'new_owner_id']);
    }

    public function test_transfer_owner_validation_rejects_system_user_as_new_owner(): void
    {
        $this->actingAsSuperAdmin();

        $feature = $this->createFeature(['owner_id' => 1]);
        $this->createFeatureProperties($feature, ['id' => 'SYS-ONLY-001']);

        $this->postJson(self::OWNER_TRANSFER_PATH, [
            'feature_id' => $feature->id,
            'new_owner_id' => 1,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['new_owner_id']);
    }

    public function test_transfer_owner_validation_rejects_nonexistent_feature_and_user_ids(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::OWNER_TRANSFER_PATH, [
            'feature_id' => 999999,
            'new_owner_id' => 999999,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['feature_id', 'new_owner_id']);
    }

    public function test_update_properties_returns_500_when_persist_fails(): void
    {
        $this->actingAsSuperAdmin();

        $feature = $this->createFeature();
        $this->createFeatureProperties($feature, ['id' => 'PROP-FAIL']);

        FeatureProperties::updating(function () {
            throw new \RuntimeException('forced properties update failure');
        });

        $this->putJson($this->propertiesPath($feature->id), $this->validPropertiesPayload())
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در ثبت اطلاعات');
    }

    public function test_update_coordinates_returns_500_when_persist_fails(): void
    {
        $this->actingAsSuperAdmin();

        $feature = $this->createFeature();
        $this->createFeatureProperties($feature);
        $this->createGeometryWithCoordinates($feature, [
            ['x' => 1, 'y' => 2],
            ['x' => 3, 'y' => 4],
            ['x' => 5, 'y' => 6],
        ]);

        \App\Models\Coordinate::updating(function () {
            throw new \RuntimeException('forced coordinates update failure');
        });

        $this->putJson($this->coordinatesPath($feature->id), [
            'coordinates' => [
                ['x' => 10.1, 'y' => 20.2],
                ['x' => 30.3, 'y' => 40.4],
                ['x' => 50.5, 'y' => 60.6],
            ],
        ])
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در ثبت اطلاعات');
    }

    public function test_transfer_owner_returns_500_when_service_throws_unexpected_exception(): void
    {
        $this->actingAsSuperAdmin();

        $feature = $this->createFeature(['owner_id' => 1]);
        $this->createFeatureProperties($feature, ['id' => 'XFER-FAIL']);
        $newOwner = $this->createCitizenUser(['name' => 'Fail Owner']);

        $this->mock(LandOwnerTransferService::class, function ($mock) {
            $mock->shouldReceive('transferOwner')
                ->once()
                ->andThrow(new \RuntimeException('unexpected transfer failure'));
        });

        $this->postJson(self::OWNER_TRANSFER_PATH, [
            'feature_id' => $feature->id,
            'new_owner_id' => $newOwner->id,
        ])
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در انتقال مالکیت');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function propertiesPath(int $featureId): string
    {
        return '/api/lands/features/'.$featureId.'/properties';
    }

    private function coordinatesPath(int $featureId): string
    {
        return '/api/lands/features/'.$featureId.'/coordinates';
    }

    /**
     * @return array<string, mixed>
     */
    private function validPropertiesPayload(array $overrides = []): array
    {
        return array_merge([
            'area' => 150,
            'density' => 8,
            'karbari' => 'm',
            'address' => 'Valid address',
            'rgb' => '#7C3AED',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function validCoordinatesPayload(array $overrides = []): array
    {
        return array_merge([
            'coordinates' => [
                ['x' => 1.0, 'y' => 2.0],
                ['x' => 3.0, 'y' => 4.0],
                ['x' => 5.0, 'y' => 6.0],
            ],
        ], $overrides);
    }
}
