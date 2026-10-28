<?php

namespace Tests\Unit\Level;

use App\Models\Level\LevelGeneralInfo;
use App\Models\Level\LevelGem;
use App\Models\Level\LevelGift;
use App\Repositories\LevelGeneralInfoRepository;
use App\Repositories\LevelGemRepository;
use App\Repositories\LevelGiftRepository;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LevelRepositoriesTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[DataProvider('repositoryCases')]
    public function test_remove_fbx_file_entry_handles_string_and_missing_urls(string $repositoryClass, string $modelClass): void
    {
        $repository = Mockery::mock($repositoryClass)->makePartial();
        $repository->shouldReceive('update')->twice();

        $model = new $modelClass;
        $model->setAttribute('fbx_file', [
            'string-entry' => 'https://cdn.example/model.fbx',
            'array-entry' => ['type' => 'png'],
        ]);

        $this->assertSame(
            ['found' => true, 'url' => 'https://cdn.example/model.fbx'],
            $repository->removeFbxFileEntry($model, 'string-entry')
        );
        $this->assertSame(
            ['found' => true, 'url' => null],
            $repository->removeFbxFileEntry($model, 'array-entry')
        );
        $this->assertSame(
            ['found' => false, 'url' => null],
            $repository->removeFbxFileEntry($model, 'missing')
        );
    }

    /** @return array<string, array{string, string}> */
    public static function repositoryCases(): array
    {
        return [
            'gem' => [LevelGemRepository::class, LevelGem::class],
            'gift' => [LevelGiftRepository::class, LevelGift::class],
            'general info' => [LevelGeneralInfoRepository::class, LevelGeneralInfo::class],
        ];
    }
}
