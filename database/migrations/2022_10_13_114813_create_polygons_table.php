<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Morilog\Jalali\Jalalian;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('polygons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('karbari');
            $table->string('publish_date')->default(Jalalian::forge(now())->format('Y/d/m'));
            $table->string('publisher_name');
            $table->integer('polygon_count');
            $table->unsignedBigInteger('total_area');
            $table->string('first_id');
            $table->string('last_id');
            $table->tinyInteger('status')->default(0);
            $table->string('fileName');
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
        Schema::dropIfExists('polygons');
    }
};
