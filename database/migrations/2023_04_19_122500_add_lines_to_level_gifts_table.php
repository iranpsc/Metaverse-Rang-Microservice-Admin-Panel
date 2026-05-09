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
        Schema::table('level_gifts', function (Blueprint $table) {
            $table->string('three_d_model_volume');
            $table->integer('three_d_model_points');
            $table->integer('three_d_model_lines');
            $table->boolean('has_animation');
            $table->string('png_file');
            $table->string('fbx_file');
            $table->boolean('rent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('level_gifts', function (Blueprint $table) {
            $table->dropColumn([
                'three_d_model_volume',
                'three_d_model_points',
                'three_d_model_lines',
                'has_animation',
                'png_file',
                'fbx_file',
                'rent',
            ]);
        });
    }
};
