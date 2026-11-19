<?php

namespace App\Services\Challenge;

use App\Models\Challenge\Question;
use Illuminate\Support\Facades\DB;

class ChallengeQuestionImportService
{
    private const ANSWER_TITLE_COLUMNS = [4, 6, 8, 10];

    private const CORRECT_ANSWER_COLUMN = 11;

    private const REQUIRED_COLUMN_COUNT = 14;

    public function import(array $rows): void
    {
        $headerIndex = array_key_first($rows);
        $header = $rows[$headerIndex] ?? [];
        $answerNumbers = null;

        foreach ($rows as $index => $row) {
            if ($index === $headerIndex || count($row) < self::REQUIRED_COLUMN_COUNT || in_array('', $row)) {
                continue;
            }

            $code = trim($row[0]);
            if ($code === '') {
                continue;
            }

            $question = [
                'code' => $code,
                'image' => trim($row[1]),
                'title' => $row[2],
                'creator_code' => trim($row[12]),
                'prize' => trim($row[13]),
            ];

            $answerNumbers ??= array_map(
                static fn (int $column): int => (int) explode('_', $header[$column])[1],
                self::ANSWER_TITLE_COLUMNS,
            );
            $correctAnswer = (int) $row[self::CORRECT_ANSWER_COLUMN];
            $answers = [];
            foreach (self::ANSWER_TITLE_COLUMNS as $position => $column) {
                $answers[] = [
                    'image' => trim($row[$column - 1]),
                    'title' => $row[$column],
                    'is_correct' => (int) ($answerNumbers[$position] === $correctAnswer),
                ];
            }

            $this->saveQuestion($question, $answers);
        }
    }

    private function saveQuestion(array $attributes, array $answers): void
    {
        DB::transaction(function () use ($attributes, $answers) {
            // The upsert locks the question while its answers are updated.
            Question::query()->upsert([$attributes], ['code'], ['image', 'title', 'creator_code', 'prize']);

            $question = Question::query()->where('code', $attributes['code'])->firstOrFail(['id']);
            $questionAnswers = $question->answers();
            $existingAnswers = $questionAnswers->orderBy('id')->limit(count($answers))->get();

            foreach ($answers as $position => $answer) {
                $existing = $existingAnswers->get($position) ?? $questionAnswers->make();
                $existing->fill($answer)->save();
            }
        }, attempts: 3);
    }
}
