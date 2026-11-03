<?php

namespace Tests\Concerns;

use App\Models\Comission;
use App\Models\SellFeatureRequest;
use App\Models\Trade;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

trait CreatesLandsMarketApiSchema
{
    use CreatesLandsApiSchema;

    protected function setUpLandsMarketApiSchema(): void
    {
        $this->setUpLandsApiSchema();
        $this->createTradesTable();
        $this->createComissionsTable();
        $this->createSellFeatureRequestsTable();
    }

    private function createTradesTable(): void
    {
        if (Schema::hasTable('trades')) {
            return;
        }

        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_id');
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('seller_id');
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    private function createComissionsTable(): void
    {
        if (Schema::hasTable('comissions')) {
            return;
        }

        Schema::create('comissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trade_id');
            $table->decimal('amount', 16, 4)->nullable();
            $table->timestamps();
        });
    }

    private function createSellFeatureRequestsTable(): void
    {
        if (Schema::hasTable('sell_feature_requests')) {
            return;
        }

        Schema::create('sell_feature_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->unsignedBigInteger('feature_id');
            $table->integer('status')->default(0);
            $table->text('note')->nullable();
            $table->decimal('price_psc', 16, 4)->nullable();
            $table->decimal('price_irr', 16, 4)->nullable();
            $table->timestamps();
        });
    }

    protected function createTrade(array $overrides = []): Trade
    {
        $featureId = $overrides['feature_id'] ?? $this->createLandWithProperties()->feature_id;
        unset($overrides['feature_id']);

        $buyerId = $overrides['buyer_id'] ?? $this->createCitizenUser(['name' => 'Buyer'])->id;
        unset($overrides['buyer_id']);

        $sellerId = $overrides['seller_id'] ?? $this->createCitizenUser(['name' => 'Seller'])->id;
        unset($overrides['seller_id']);

        return Trade::create(array_merge([
            'feature_id' => $featureId,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'date' => now()->toDateString(),
        ], $overrides));
    }

    protected function createTradeAt(Carbon $createdAt, array $overrides = []): Trade
    {
        $trade = $this->createTrade($overrides);

        Trade::query()->whereKey($trade->id)->update([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        return $trade->fresh();
    }

    protected function createComission(Trade $trade, array $overrides = []): Comission
    {
        return Comission::create(array_merge([
            'trade_id' => $trade->id,
            'amount' => 100,
        ], $overrides));
    }

    protected function createSellFeatureRequest(array $overrides = []): SellFeatureRequest
    {
        $featureId = $overrides['feature_id'] ?? $this->createLandWithProperties()->feature_id;
        unset($overrides['feature_id']);

        $sellerId = $overrides['seller_id'] ?? $this->createCitizenUser(['name' => 'Pricing Seller'])->id;
        unset($overrides['seller_id']);

        return SellFeatureRequest::create(array_merge([
            'seller_id' => $sellerId,
            'buyer_id' => null,
            'feature_id' => $featureId,
            'status' => 0,
            'note' => 'Pending pricing request',
            'price_psc' => 500,
            'price_irr' => 1000000,
        ], $overrides));
    }
}
