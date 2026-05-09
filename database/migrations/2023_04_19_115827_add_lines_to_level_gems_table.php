<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('level_gems', function (Blueprint $table) {
            $table->string('volume')->change();
            $table->boolean('has_animation')->after('color');
            $table->integer('lines')->after('has_animation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('level_gems', function (Blueprint $table) {
            $table->dropColumn(['has_animation', 'lines']);
        });
    }
};
