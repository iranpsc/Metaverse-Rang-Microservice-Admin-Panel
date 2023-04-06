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
        Schema::create('level_general_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Level::class);
            $table->unsignedInteger('score');
            $table->longText('description');
            $table->unsignedInteger('rank');
            $table->unsignedInteger('subcategories');
            $table->string('persian_font');
            $table->string('english_font');
            $table->unsignedInteger('file_volume');
            $table->string('used_colors');
            $table->integer('points');
            $table->string('designer');
            $table->string('model_designer');
            $table->date('creation_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('level_general_infos');
    }
};
