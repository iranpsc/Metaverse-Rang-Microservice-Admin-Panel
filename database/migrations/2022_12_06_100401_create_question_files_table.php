<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_files', function (Blueprint $table) {
            $table->id();
            $table->string('question_code');
            $table->string('question_image');
            $table->string('question');
            $table->string('answer_one_image');
            $table->string('answer_one');
            $table->string('answer_two_image');
            $table->string('answer_two');
            $table->string('answer_three_image');
            $table->string('answer_three');
            $table->string('answer_four_image');
            $table->string('answer_four');
            $table->string('correct_answer');
            $table->string('admin_id');
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
        Schema::dropIfExists('question_files');
    }
};
