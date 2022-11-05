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
        Schema::table('maps', function(Blueprint $table) {
            $table->dropColumn('type');
            $table->string('karbari');
            $table->date('publish_date');
            $table->string('publisher_name');
            $table->bigInteger('polygon_count');
            $table->bigInteger('total_area');
            $table->string('first_id');
            $table->string('last_id');
            $table->tinyInteger('status')->default(0);
            $table->string('fileName');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
