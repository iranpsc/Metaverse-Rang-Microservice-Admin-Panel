<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesLevelsApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpLevelsApiSchema(): void
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
            $table->unsignedBigInteger('psc')->default(0);
            $table->unsignedBigInteger('yellow')->default(0);
            $table->unsignedBigInteger('blue')->default(0);
            $table->unsignedBigInteger('red')->default(0);
            $table->unsignedBigInteger('effect')->default(0);
            $table->float('satisfaction')->default(0);
            $table->timestamps();
        });
    }
}
