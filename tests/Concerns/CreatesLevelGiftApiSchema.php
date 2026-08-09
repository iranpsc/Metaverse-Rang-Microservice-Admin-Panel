<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesLevelGiftApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpLevelGiftApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createLevelsTable();
        $this->createLevelGiftsTable();
    }

    private function createLevelsTable(): void
    {
        if (Schema::hasTable('levels')) {
            return;
        }

        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('score')->default('0');
            $table->string('background_image')->default('');
            $table->timestamps();
        });
    }

    private function createLevelGiftsTable(): void
    {
        if (Schema::hasTable('level_gifts')) {
            return;
        }

        Schema::create('level_gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('level_id');
            $table->string('name');
            $table->longText('description');
            $table->integer('monthly_capacity_count');
            $table->boolean('store_capacity');
            $table->boolean('sell_capacity');
            $table->text('features');
            $table->boolean('sell');
            $table->boolean('vod_document_registration');
            $table->string('seller_link');
            $table->string('designer');
            $table->decimal('three_d_model_volume', 12, 4)->default(0);
            $table->integer('three_d_model_points')->default(0);
            $table->integer('three_d_model_lines')->default(0);
            $table->boolean('has_animation')->default(false);
            $table->string('png_file')->nullable();
            $table->text('fbx_file')->nullable();
            $table->string('gif_file')->nullable();
            $table->boolean('rent')->default(false);
            $table->integer('vod_count')->default(0);
            $table->string('start_vod_id')->nullable();
            $table->string('end_vod_id')->nullable();
            $table->timestamps();

            $table->index('level_id');
        });
    }
}
