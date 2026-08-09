<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesLevelGeneralInfoApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpLevelGeneralInfoApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createLevelsTable();
        $this->createLevelGeneralInfosTable();
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

    private function createLevelGeneralInfosTable(): void
    {
        if (Schema::hasTable('level_general_infos')) {
            return;
        }

        Schema::create('level_general_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('level_id');
            $table->integer('score');
            $table->longText('description');
            $table->integer('rank');
            $table->integer('subcategories');
            $table->string('persian_font');
            $table->string('english_font');
            $table->decimal('file_volume', 12, 3)->default(0);
            $table->string('used_colors', 500);
            $table->integer('points');
            $table->string('designer');
            $table->string('model_designer');
            $table->string('creation_date');
            $table->integer('lines');
            $table->boolean('has_animation')->default(false);
            $table->string('png_file')->nullable();
            $table->text('fbx_file')->nullable();
            $table->string('gif_file')->nullable();

            $table->index('level_id');
        });
    }
}
