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
        Schema::create('level_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Level::class);
            $table->boolean('create_union');
            $table->boolean('add_memeber_to_union');
            $table->boolean('observation_license');
            $table->boolean('gate_license');
            $table->boolean('lawyer_license');
            $table->boolean('city_counsile_entry');
            $table->boolean('establish_special_residential_property');
            $table->boolean('establish_property_on_surface');
            $table->boolean('judge_entry');
            $table->boolean('upload_image');
            $table->boolean('delete_image');
            $table->boolean('inter_level_general_points');
            $table->boolean('inter_level_special_points');
            $table->boolean('rent_out_satisfaction');
            $table->boolean('access_to_answer_questions_unit');
            $table->boolean('create_challenge_questions');
            $table->boolean('upload_music');
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
        Schema::dropIfExists('level_licenses');
    }
};
