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
        Schema::create('level_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Level::class);
            $table->string('name');
            $table->longText('description');
            $table->integer('monthly_capacity_count');
            $table->boolean('store_capacity');
            $table->boolean('sell_capacity');
            $table->text('features');
            $table->boolean('sell');
            $table->boolean('vod_document_registration');
            $table->string('seller_link');
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
        Schema::dropIfExists('level_gifts');
    }
};
