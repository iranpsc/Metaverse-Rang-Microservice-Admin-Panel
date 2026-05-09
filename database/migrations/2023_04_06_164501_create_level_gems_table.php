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
        Schema::create('level_gems', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Level::class);
            $table->string('name');
            $table->longText('description');
            $table->string('thread');
            $table->integer('points');
            $table->integer('volume');
            $table->string('color');
            $table->string('png_file');
            $table->string('fbx_file');
            $table->boolean('encryption');
            $table->string('designer');
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
        Schema::dropIfExists('level_gems');
    }
};
