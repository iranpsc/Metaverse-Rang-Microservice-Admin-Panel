<?php

namespace Tests\Unit\Models;

use App\Models\BulkMessageLog;
use App\Models\Feature;
use App\Models\FeatureLimit;
use App\Models\FeatureHourlyProfit;
use App\Models\Geometry;
use App\Models\Map;
use App\Models\Option;
use App\Models\SellFeatureRequest;
use App\Models\Ticket;
use App\Models\Variable;
use App\Models\Video;
use App\Models\VideoCategory;
use App\Models\Wallet;
use App\Models\Dynasty\DynastyPrize;
use App\Models\Translations\Modal;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tests\TestCase;

class ThinModelRelationsTest extends TestCase
{
    /**
     * @return array<string, array{object, list<string>}>
     */
    public static function relationCases(): array
    {
        return [
            'geometry' => [new Geometry, ['feature', 'coordinates']],
            'wallet' => [new Wallet, ['user', 'variable']],
            'modal' => [new Modal, ['translation', 'tabs']],
            'video category' => [new VideoCategory, ['subCategories', 'image']],
            'sell request' => [new SellFeatureRequest, ['seller', 'buyer', 'feature']],
            'map' => [new Map, ['features']],
            'feature hourly profit' => [new FeatureHourlyProfit, ['user', 'feature']],
            'bulk message log' => [new BulkMessageLog, ['user']],
            'ticket' => [new Ticket, ['sender', 'reciever', 'responser', 'responses']],
            'video' => [new Video, ['category', 'interactions', 'views']],
            'feature' => [new Feature, ['map', 'properties', 'geometry', 'images', 'owner', 'buyRequests', 'lockedAssets']],
            'option' => [new Option, ['variable', 'priceChangeLogs', 'image']],
            'dynasty prize' => [new DynastyPrize, ['receivedPrizes']],
            'variable' => [new Variable, ['option', 'priceChangeLogs', 'image']],
        ];
    }

    /** @param object $model @param list<string> $methods */
    #[\PHPUnit\Framework\Attributes\DataProvider('relationCases')]
    public function test_model_relations_return_eloquent_relations(object $model, array $methods): void
    {
        foreach ($methods as $method) {
            $this->assertInstanceOf(Relation::class, $model->{$method}());
        }
    }

    public function test_feature_limit_expiry_and_deletability_are_defined_for_boundaries(): void
    {
        $noEndDate = new FeatureLimit;
        $this->assertFalse($noEndDate->isExpired());

        $active = new FeatureLimit;
        $active->start_date = now()->subDay();
        $active->end_date = now()->addDay();
        $this->assertFalse($active->isExpired());
        $this->assertTrue($active->isDeletable());

        $expired = new FeatureLimit;
        $expired->start_date = now()->subDays(2);
        $expired->end_date = now()->subDay();
        $this->assertTrue($expired->isExpired());
        $this->assertFalse($expired->isDeletable());

        $stringDate = new FeatureLimit;
        $stringDate->end_date = now()->subDay()->toDateString();
        $this->assertTrue($stringDate->isExpired());
    }

    public function test_map_publication_status_and_dynasty_titles_are_mapped(): void
    {
        $published = new Map(['status' => 1]);
        $unpublished = new Map(['status' => 0]);

        $this->assertTrue($published->isPublished());
        $this->assertFalse($unpublished->isPublished());

        $titles = [
            'father' => 'پدر',
            'mother' => 'مادر',
            'life_partner' => 'همسر',
            'brother' => 'برادر',
            'sister' => 'خواهر',
            'offspring' => 'فرزند',
            'wife' => 'زن',
            'husband' => 'شوهر',
        ];

        foreach ($titles as $member => $title) {
            $this->assertSame($title, (new DynastyPrize(['member' => $member]))->getRelationTitle());
        }
    }

    public function test_feature_sell_request_relation_is_invoked_for_regression_visibility(): void
    {
        $this->expectException(\Error::class);

        (new Feature)->sellRequests();
    }

    public function test_map_crs_relation_is_invoked_for_regression_visibility(): void
    {
        $this->expectException(\Error::class);

        (new Map)->crs();
    }
}
