<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesChallengeQuestionsApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpChallengeQuestionsApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createQuestionsTable();
        $this->createAnswersTable();
    }

    private function createQuestionsTable(): void
    {
        if (Schema::hasTable('questions')) {
            return;
        }

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('image')->nullable();
            $table->string('creator_code')->nullable();
            $table->integer('prize')->default(0);
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('participants')->default(0);
            $table->timestamps();
        });
    }

    private function createAnswersTable(): void
    {
        if (Schema::hasTable('answers')) {
            return;
        }

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }
}
