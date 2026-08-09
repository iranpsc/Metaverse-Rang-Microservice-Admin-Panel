<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesUserLevelsApiSchema
{
    use CreatesLevelsApiSchema;

    protected function setUpUserLevelsApiSchema(): void
    {
        $this->setUpLevelsApiSchema();
        $this->ensureUsersScoreColumn();
        $this->createLevelUserPivotTable();
    }

    private function ensureUsersScoreColumn(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'score')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('score')->default(0);
            });
        }
    }

    private function createLevelUserPivotTable(): void
    {
        if (Schema::hasTable('level_user')) {
            return;
        }

        Schema::create('level_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('level_id');
            $table->timestamps();
        });
    }
}
