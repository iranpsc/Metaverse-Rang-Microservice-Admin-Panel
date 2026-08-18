<?php

namespace Tests\Unit\Models;

use App\Models\Feature;
use App\Models\FeatureProperties;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FeaturePropertiesModelTest extends TestCase
{
    public function test_feature_relation_is_belongs_to(): void
    {
        $properties = new FeatureProperties;

        $this->assertInstanceOf(BelongsTo::class, $properties->feature());
        $this->assertSame(Feature::class, $properties->feature()->getRelated()::class);
    }

    #[DataProvider('applicationTitleProvider')]
    public function test_get_application_title_maps_karbari_codes(string $karbari, string $title): void
    {
        $properties = new FeatureProperties(['karbari' => $karbari]);

        $this->assertSame($title, $properties->getApplicationTitle());
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function applicationTitleProvider(): array
    {
        return [
            'commercial' => ['t', 'تجاری'],
            'education' => ['a', 'آموزشی'],
            'green' => ['s', 'فضای سبز'],
            'residential' => ['m', 'مسکونی'],
            'health' => ['b', 'بهداشتی'],
            'office' => ['e', 'اداری'],
            'culture' => ['f', 'فرهنگی'],
            'tourism' => ['g', 'گردشگری'],
            'religious' => ['z', 'مذهبی'],
            'exhibition' => ['n', 'نمایشگاه'],
        ];
    }
}
