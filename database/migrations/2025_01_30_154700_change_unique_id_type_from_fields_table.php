<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'sqlite';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('new_fields');
        Schema::create('new_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tab_id');
            $table->string('name');
            $table->text('translation')->nullable();
            $table->unsignedBigInteger('unique_id')->nullable();
            $table->foreign('tab_id')->references('id')->on('tabs')->onDelete('cascade');
        });

        DB::table('fields')->orderBy('id')->chunk(100, function ($fields) {
            foreach ($fields as $field) {
                DB::table('new_fields')->insert((array) $field);
            }
        });

        Schema::drop('fields');
        Schema::rename('new_fields', 'fields');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('old_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tab_id');
            $table->string('name');
            $table->text('translation')->nullable();
            $table->string('unique_id');
            $table->foreign('tab_id')->references('id')->on('tabs')->onDelete('cascade');
        });

        DB::table('fields')->orderBy('id')->chunk(100, function ($fields) {
            foreach ($fields as $field) {
                DB::table('old_fields')->insert((array) $field);
            }
        });

        Schema::drop('fields');
        Schema::rename('old_fields', 'fields');
    }
};
