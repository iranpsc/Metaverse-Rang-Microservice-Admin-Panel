<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesLevelPrizeApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpLevelPrizeApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createLevelsTable();
        $this->createLevelPrizesTable();
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

    private function createLevelPrizesTable(): void
    {
        if (Schema::hasTable('level_prizes')) {
            return;
        }

        Schema::create('level_prizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('level_id');
            $table->unsignedBigInteger('psc');
            $table->unsignedBigInteger('yellow');
            $table->unsignedBigInteger('blue');
            $table->unsignedBigInteger('red');
            $table->unsignedBigInteger('effect');
            $table->float('satisfaction');
            $table->timestamps();
        });
    }
}
