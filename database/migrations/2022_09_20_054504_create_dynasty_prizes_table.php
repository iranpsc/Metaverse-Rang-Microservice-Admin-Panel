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
        Schema::create('dynasty_prizes', function (Blueprint $table) {
            $table->id();
            $table->string('member');
            $table->float('satisfaction');
            $table->float('introduction_profit_increase');
            $table->float('accumulated_capital_reserve');
            $table->float('data_storage');
            $table->integer('psc');
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
        Schema::dropIfExists('dynasty_prizes');
    }
};
