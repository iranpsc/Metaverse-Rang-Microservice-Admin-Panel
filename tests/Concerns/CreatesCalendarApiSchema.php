<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesCalendarApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpCalendarApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createCalendarsTable();
        $this->createInteractionsTable();
        $this->createViewsTable();
    }

    private function createCalendarsTable(): void
    {
        if (Schema::hasTable('calendars')) {
            return;
        }

        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('color')->default('#000000');
            $table->string('writer')->default('Admin');
            $table->boolean('is_version')->default(false);
            $table->string('version_title')->nullable();
            $table->string('btn_name')->nullable();
            $table->string('btn_link')->nullable();
            $table->string('image')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();
        });
    }

    private function createInteractionsTable(): void
    {
        if (Schema::hasTable('interactions')) {
            return;
        }

        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->string('likeable_type');
            $table->unsignedBigInteger('likeable_id');
            $table->boolean('liked')->default(false);
            $table->timestamps();
            $table->index(['likeable_type', 'likeable_id']);
        });
    }

    private function createViewsTable(): void
    {
        if (Schema::hasTable('views')) {
            return;
        }

        Schema::create('views', function (Blueprint $table) {
            $table->id();
            $table->string('viewable_type');
            $table->unsignedBigInteger('viewable_id');
            $table->timestamps();
            $table->index(['viewable_type', 'viewable_id']);
        });
    }
}
