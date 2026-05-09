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
            $table->string('gif_file')->nullable()->after('fbx_file');
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
            $table->dropColumn('gif_file');
        });
    }
};
