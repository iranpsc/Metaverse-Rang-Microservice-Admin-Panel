<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Specify the connection for this migration
     */
    protected $connection = 'sqlite';

    /**
     * Run the migrations to drop the name field from fields table
     */
    public function up(): void
    {
        // SQLite doesn't support dropping columns directly, so we need to recreate the table
        Schema::connection($this->connection)->create('new_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tab_id');
            $table->text('translation')->nullable();
            $table->unsignedBigInteger('unique_id')->nullable();
            $table->foreign('tab_id')->references('id')->on('tabs')->onDelete('cascade');
        });

        // Copy data from the old table to the new one, excluding the name field
        DB::connection($this->connection)->table('fields')->orderBy('id')->each(function ($field) {
            DB::connection($this->connection)->table('new_fields')->insert([
                'id' => $field->id,
                'tab_id' => $field->tab_id,
                'translation' => $field->translation,
                'unique_id' => $field->unique_id,
            ]);
        });

        // Drop old table and rename new table
        Schema::connection($this->connection)->drop('fields');
        Schema::connection($this->connection)->rename('new_fields', 'fields');
    }

    /**
     * Reverse the migrations - recreate the name field if needed
     */
    public function down(): void
    {
        Schema::connection($this->connection)->create('old_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tab_id');
            $table->string('name');
            $table->text('translation')->nullable();
            $table->unsignedBigInteger('unique_id')->nullable();
            $table->foreign('tab_id')->references('id')->on('tabs')->onDelete('cascade');
        });

        // Copy data back with empty name field
        DB::connection($this->connection)->table('fields')->orderBy('id')->each(function ($field) {
            DB::connection($this->connection)->table('old_fields')->insert([
                'id' => $field->id,
                'tab_id' => $field->tab_id,
                'name' => '', // Empty name since we don't have the old values
                'translation' => $field->translation,
                'unique_id' => $field->unique_id,
            ]);
        });

        // Drop new table and rename old table
        Schema::connection($this->connection)->drop('fields');
        Schema::connection($this->connection)->rename('old_fields', 'fields');
    }
};
