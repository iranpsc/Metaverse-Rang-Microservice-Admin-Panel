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
        Schema::table('level_general_infos', function (Blueprint $table) {
            $table->float('file_volume')->change();
            $table->integer('lines')->after('points');
            $table->boolean('has_animation')->after('lines');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('level_general_infos', function (Blueprint $table) {
            $table->dropColumn(['lines', 'has_animation']);
        });
    }
};
