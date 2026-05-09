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
        Schema::table('question_answers', function (Blueprint $table) {
            $table->renameColumn('answer', 'title');
            $table->string('image');
            $table->boolean('is_correct')->default(false);
        });
        Schema::rename('question_answers', 'answers');
        Schema::dropIfExists('challenge_prize_lists');
        Schema::dropIfExists('challenge_prizes_lists');
        Schema::dropIfExists('question_files');
        Schema::dropIfExists('question_prizes');
        Schema::dropIfExists('question_times');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('question_answers', function (Blueprint $table) {
            //
        });
    }
};
