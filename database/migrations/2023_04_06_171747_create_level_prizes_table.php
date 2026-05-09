<?php

use App\Models\Level\Level;
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
        Schema::create('level_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Level::class);
            $table->unsignedBigInteger('psc');
            $table->unsignedBigInteger('yellow');
            $table->unsignedBigInteger('blue');
            $table->unsignedBigInteger('red');
            $table->unsignedBigInteger('effect');
            $table->float('satisfaction');
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
        Schema::dropIfExists('level_prizes');
    }
};
