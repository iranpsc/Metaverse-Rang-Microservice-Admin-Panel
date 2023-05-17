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
        Schema::table('maps', function (Blueprint $table) {
            $table->dropTimestamps();
            $table->longText('central_point_coordinates')->nullable();
            $table->longText('border_coordinates')->nullable();
            $table->unsignedBigInteger('polygon_area')->default(0);
            $table->text('polygon_address')->nullable();
            $table->string('polygon_color')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->timestamps();
            $table->dropColumn('central_point_coordinates');
            $table->dropColumn('border_coordinates');
            $table->dropColumn('polygon_area');
            $table->dropColumn('polygon_address');
            $table->dropColumn('polygon_color');
        });
    }
};
