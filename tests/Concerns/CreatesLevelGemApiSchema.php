<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

trait CreatesLevelGemApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpLevelGemApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createLevelsTable();
        $this->createLevelGemsTable();
        Storage::fake('public');
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

    private function createLevelGemsTable(): void
    {
        if (Schema::hasTable('level_gems')) {
            return;
        }

        Schema::create('level_gems', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('level_id');
            $table->string('name');
            $table->longText('description');
            $table->string('thread');
            $table->integer('points');
            $table->string('volume');
            $table->string('color');
            $table->string('png_file')->nullable();
            $table->text('fbx_file')->nullable();
            $table->boolean('encryption');
            $table->boolean('has_animation');
            $table->integer('lines');
            $table->string('designer');
            $table->timestamps();
        });
    }
}
