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
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropTimestamps();
            $table->dropColumn(['start_date', 'end_date', 'start_time', 'end_time', 'views']);
            $table->boolean('is_version')->default(false);
            $table->string('version_title')->nullable();
            $table->string('btn_name')->nullable();
            $table->string('btn_link')->nullable();
            $table->string('image')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calendars', function (Blueprint $table) {
            $table->timestamps();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('views');
            $table->dropColumn(['is_version', 'version_title', 'btn_name', 'btn_link', 'image', 'starts_at', 'ends_at']);
        });
    }
};
