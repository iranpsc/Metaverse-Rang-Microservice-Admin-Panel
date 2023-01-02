<?php

use App\Models\StatisticesSettings;
use App\Models\StatisticesTypes;
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
        Schema::create('statistices_types_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StatisticesSettings::class);
            $table->foreignIdFor(StatisticesTypes::class);
            $table->boolean('status')->default(0);
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
        Schema::dropIfExists('statistices_types_settings');
    }
};
