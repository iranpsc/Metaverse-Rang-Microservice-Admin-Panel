<?php

namespace Tests\Unit\ChallengeQuestions;

use App\Jobs\ImportChallengeQuestions;
use App\Models\Challenge\Answer;
use App\Models\Challenge\Question;
use Tests\Concerns\CreatesChallengeQuestionsApiSchema;
use Tests\TestCase;

class ImportChallengeQuestionsJobTest extends TestCase
{
    use CreatesChallengeQuestionsApiSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpChallengeQuestionsApiSchema();
    }

    public function test_handle_skips_header_and_creates_question_with_four_answers(): void
    {
        $data = [
            $this->headerRow(),
            $this->dataRow([
                'code' => 'IMP-001',
                'title' => 'Imported challenge',
                'correct' => 2,
                'creator_code' => '5555',
                'prize' => '2500',
            ]),
        ];

        (new ImportChallengeQuestions($data))->handle();

        $this->assertDatabaseHas('questions', [
            'code' => 'IMP-001',
            'title' => 'Imported challenge',
            'creator_code' => '5555',
            'prize' => 2500,
        ]);

        $question = Question::query()->where('code', 'IMP-001')->first();
        $this->assertNotNull($question);
        $this->assertCount(4, $question->answers);

        $answers = $question->answers()->orderBy('id')->get();
        $this->assertSame('Answer one', $answers[0]->title);
        $this->assertFalse((bool) $answers[0]->is_correct);
        $this->assertSame('Answer two', $answers[1]->title);
        $this->assertTrue((bool) $answers[1]->is_correct);
        $this->assertFalse((bool) $answers[2]->is_correct);
        $this->assertFalse((bool) $answers[3]->is_correct);
    }

    public function test_handle_skips_empty_rows(): void
    {
        $emptyRow = array_fill(0, 14, '');
        $data = [
            $this->headerRow(),
            $emptyRow,
            $this->dataRow([
                'code' => 'IMP-002',
                'title' => 'Only real row',
                'correct' => 1,
            ]),
        ];

        (new ImportChallengeQuestions($data))->handle();

        $this->assertSame(1, Question::query()->count());
        $this->assertDatabaseHas('questions', ['code' => 'IMP-002']);
        $this->assertSame(4, Answer::query()->count());
    }

    public function test_handle_marks_correct_answer_from_column_eleven(): void
    {
        $data = [
            $this->headerRow(),
            $this->dataRow([
                'code' => 'IMP-003',
                'title' => 'Correct is fourth',
                'correct' => 4,
            ]),
        ];

        (new ImportChallengeQuestions($data))->handle();

        $question = Question::query()->where('code', 'IMP-003')->firstOrFail();
        $answers = $question->answers()->orderBy('id')->get();

        $this->assertFalse((bool) $answers[0]->is_correct);
        $this->assertFalse((bool) $answers[1]->is_correct);
        $this->assertFalse((bool) $answers[2]->is_correct);
        $this->assertTrue((bool) $answers[3]->is_correct);
    }

    /**
     * @return list<string>
     */
    private function headerRow(): array
    {
        return [
            'code',
            'image',
            'title',
            'answer_image_1',
            'option_1',
            'answer_image_2',
            'option_2',
            'answer_image_3',
            'option_3',
            'answer_image_4',
            'option_4',
            'correct',
            'creator_code',
            'prize',
        ];
    }

    /**
     * @param  array{code?: string, title?: string, correct?: int, creator_code?: string, prize?: string}  $overrides
     * @return list<string|int>
     */
    private function dataRow(array $overrides = []): array
    {
        return [
            $overrides['code'] ?? 'IMP-DEFAULT',
            'questions/image.png',
            $overrides['title'] ?? 'Default title',
            'answers/1.png',
            'Answer one',
            'answers/2.png',
            'Answer two',
            'answers/3.png',
            'Answer three',
            'answers/4.png',
            'Answer four',
            $overrides['correct'] ?? 1,
            $overrides['creator_code'] ?? '1000',
            $overrides['prize'] ?? '1000',
        ];
    }
}
