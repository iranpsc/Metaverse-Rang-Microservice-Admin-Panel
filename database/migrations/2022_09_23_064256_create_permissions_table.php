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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->boolean("BFR");
            $table->boolean("SF");
            $table->boolean("W");
            $table->boolean("JU");
            $table->boolean( "DM");
            $table->boolean("PIUP");
            $table->boolean("PITC");
            $table->boolean("PIC");
            $table->boolean("ESOO");
            $table->boolean("COTB");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions');
    }
};
