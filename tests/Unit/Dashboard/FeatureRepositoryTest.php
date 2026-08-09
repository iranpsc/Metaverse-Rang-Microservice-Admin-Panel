<?php

namespace Tests\Unit\Dashboard;

use App\Models\Feature;
use App\Repositories\FeatureRepository;
use Tests\Concerns\CreatesDashboardSchema;
use Tests\TestCase;

class FeatureRepositoryTest extends TestCase
{
    use CreatesDashboardSchema;

    private FeatureRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDashboardSchema();
        $this->repository = new FeatureRepository;
    }

    public function test_all_and_sold_return_zero_when_empty(): void
    {
        $this->assertSame(0, $this->repository->all());
        $this->assertSame(0, $this->repository->sold());
    }

    public function test_all_counts_every_feature(): void
    {
        Feature::create(['map_id' => 1, 'type' => 'land', 'owner_id' => 1]);
        Feature::create(['map_id' => 1, 'type' => 'land', 'owner_id' => 2]);
        Feature::create(['map_id' => 1, 'type' => 'land', 'owner_id' => 3]);

        $this->assertSame(3, $this->repository->all());
    }

    public function test_sold_excludes_system_owner_id_one(): void
    {
        Feature::create(['map_id' => 1, 'type' => 'land', 'owner_id' => 1]);
        Feature::create(['map_id' => 1, 'type' => 'land', 'owner_id' => 2]);
        Feature::create(['map_id' => 1, 'type' => 'land', 'owner_id' => 99]);

        $this->assertSame(3, $this->repository->all());
        $this->assertSame(2, $this->repository->sold());
    }

    public function test_sold_is_zero_when_all_features_are_system_owned(): void
    {
        Feature::create(['map_id' => 1, 'type' => 'land', 'owner_id' => 1]);
        Feature::create(['map_id' => 2, 'type' => 'land', 'owner_id' => 1]);

        $this->assertSame(2, $this->repository->all());
        $this->assertSame(0, $this->repository->sold());
    }

    public function test_sold_equals_all_when_no_system_owned_features(): void
    {
        Feature::create(['map_id' => 1, 'type' => 'land', 'owner_id' => 5]);
        Feature::create(['map_id' => 1, 'type' => 'building', 'owner_id' => 8]);

        $this->assertSame(2, $this->repository->all());
        $this->assertSame(2, $this->repository->sold());
    }

    public function test_sold_does_not_treat_owner_id_zero_as_system_owned(): void
    {
        Feature::create(['map_id' => 1, 'type' => 'land', 'owner_id' => 0]);
        Feature::create(['map_id' => 1, 'type' => 'land', 'owner_id' => 1]);

        $this->assertSame(2, $this->repository->all());
        $this->assertSame(1, $this->repository->sold());
    }
}
