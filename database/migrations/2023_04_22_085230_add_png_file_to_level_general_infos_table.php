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
            $table->string('png_file')->nullable()->after('has_animation');
            $table->string('fbx_file')->nullable()->after('png_file');
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
            $table->dropColumn(['png_file', 'fbx_file']);
        });
    }
};
