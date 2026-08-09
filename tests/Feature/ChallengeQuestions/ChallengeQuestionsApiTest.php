<?php

namespace Tests\Feature\ChallengeQuestions;

use App\Jobs\ImportChallengeQuestions;
use App\Models\Challenge\Answer;
use App\Models\Challenge\Question;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesChallengeQuestionsApiSchema;
use Tests\TestCase;

class ChallengeQuestionsApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesChallengeQuestionsApiSchema;

    private const INDEX_PATH = '/api/challenge/questions';

    private const IMPORT_PATH = '/api/challenge/questions/import';

    private const SUCCESS_MESSAGE = 'سوالات چالش با موفقیت دریافت شد.';

    private const IMPORT_SUCCESS_MESSAGE = 'درون‌ریزی سوالات در صف پردازش قرار گرفت.';

    private const IMPORT_EMPTY_MESSAGE = 'فایل انتخاب شده حاوی داده‌ای نیست.';

    private const IMPORT_ERROR_MESSAGE = 'بروز خطا در پردازش فایل درون‌ریزی.';

    private const DESTROY_SUCCESS_MESSAGE = 'سوال با موفقیت حذف شد.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpChallengeQuestionsApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_import_returns_unauthorized(): void
    {
        $this->postJson(self::IMPORT_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $question = $this->createQuestion();

        $this->deleteJson($this->destroyPath($question))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_index(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_index(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE);
    }

    public function test_authenticated_super_admin_can_import(): void
    {
        $this->actingAsSuperAdmin();
        Bus::fake();

        Excel::shouldReceive('toArray')
            ->once()
            ->andReturn([[
                ['code', 'image', 'title'],
                ['Q1', 'img.png', 'Title'],
            ]]);

        $this->postImport($this->fakeXlsx())
            ->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::IMPORT_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_import(): void
    {
        $this->actingAsRegularAdmin();
        Bus::fake();

        Excel::shouldReceive('toArray')
            ->once()
            ->andReturn([[
                ['code', 'image', 'title'],
                ['Q1', 'img.png', 'Title'],
            ]]);

        $this->postImport($this->fakeXlsx())
            ->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::IMPORT_SUCCESS_MESSAGE);
    }

    public function test_authenticated_super_admin_can_destroy(): void
    {
        $this->actingAsSuperAdmin();

        $question = $this->createQuestion();

        $this->deleteJson($this->destroyPath($question))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_destroy(): void
    {
        $this->actingAsRegularAdmin();

        $question = $this->createQuestion();

        $this->deleteJson($this->destroyPath($question))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Happy path / structure
    // -------------------------------------------------------------------------

    public function test_empty_dataset_returns_empty_collection_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE)
            ->assertJsonPath('data.questions', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_returns_full_json_structure_with_nested_answers(): void
    {
        $this->actingAsSuperAdmin();

        $question = $this->createQuestion([
            'code' => 'STRUCT-1',
            'title' => 'Structure question',
            'creator_code' => '4242',
            'prize' => 1500,
            'image' => 'questions/struct.png',
        ]);
        $this->createAnswer($question, [
            'title' => 'Answer A',
            'image' => 'answers/a.png',
            'is_correct' => true,
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'questions' => [
                        [
                            'id',
                            'code',
                            'title',
                            'creator_code',
                            'prize',
                            'image',
                            'created_at',
                            'updated_at',
                            'answers' => [
                                [
                                    'id',
                                    'question_id',
                                    'title',
                                    'image',
                                    'is_correct',
                                    'created_at',
                                    'updated_at',
                                ],
                            ],
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'from',
                        'to',
                    ],
                ],
            ])
            ->assertJsonPath('data.questions.0.code', 'STRUCT-1')
            ->assertJsonPath('data.questions.0.title', 'Structure question')
            ->assertJsonPath('data.questions.0.creator_code', '4242')
            ->assertJsonPath('data.questions.0.prize', 1500)
            ->assertJsonPath('data.questions.0.image', 'questions/struct.png')
            ->assertJsonPath('data.questions.0.answers.0.title', 'Answer A')
            ->assertJsonPath('data.questions.0.answers.0.is_correct', true);

        $this->assertIsBool(
            $this->getJson(self::INDEX_PATH)->json('data.questions.0.answers.0.is_correct')
        );
    }

    public function test_answers_are_ordered_by_created_at(): void
    {
        $this->actingAsSuperAdmin();

        $question = $this->createQuestion(['title' => 'Ordered answers']);

        $newer = $this->createAnswer($question, ['title' => 'Newer answer']);
        $newer->forceFill(['created_at' => now()->subHour()])->save();

        $older = $this->createAnswer($question, ['title' => 'Older answer']);
        $older->forceFill(['created_at' => now()->subDays(2)])->save();

        $middle = $this->createAnswer($question, ['title' => 'Middle answer']);
        $middle->forceFill(['created_at' => now()->subDay()])->save();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.questions.0.answers.0.id', $older->id)
            ->assertJsonPath('data.questions.0.answers.1.id', $middle->id)
            ->assertJsonPath('data.questions.0.answers.2.id', $newer->id);
    }

    public function test_questions_are_ordered_by_id_ascending(): void
    {
        $this->actingAsSuperAdmin();

        $first = $this->createQuestion(['title' => 'First']);
        $second = $this->createQuestion(['title' => 'Second']);
        $third = $this->createQuestion(['title' => 'Third']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.questions.0.id', $first->id)
            ->assertJsonPath('data.questions.1.id', $second->id)
            ->assertJsonPath('data.questions.2.id', $third->id);
    }

    // -------------------------------------------------------------------------
    // Filtering & search
    // -------------------------------------------------------------------------

    public function test_search_by_title_partial_match(): void
    {
        $this->actingAsSuperAdmin();

        $this->createQuestion(['title' => 'UniqueTitleNeedle challenge', 'code' => 'T-1']);
        $this->createQuestion(['title' => 'Other title', 'code' => 'T-2']);

        $this->getJson(self::INDEX_PATH.'?search=UniqueTitleNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.questions.0.title', 'UniqueTitleNeedle challenge');
    }

    public function test_search_by_code_partial_match(): void
    {
        $this->actingAsSuperAdmin();

        $this->createQuestion(['title' => 'Code hit', 'code' => 'CODE-NEEDLE-99']);
        $this->createQuestion(['title' => 'Code miss', 'code' => 'OTHER-01']);

        $this->getJson(self::INDEX_PATH.'?search=NEEDLE')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.questions.0.code', 'CODE-NEEDLE-99');
    }

    public function test_search_matches_title_or_code(): void
    {
        $this->actingAsSuperAdmin();

        $this->createQuestion(['title' => 'SharedNeedle in title', 'code' => 'OR-1']);
        $this->createQuestion(['title' => 'Plain title', 'code' => 'SharedNeedle-code']);
        $this->createQuestion(['title' => 'Unrelated', 'code' => 'OR-3']);

        $this->getJson(self::INDEX_PATH.'?search=SharedNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2);
    }

    public function test_search_miss_returns_empty(): void
    {
        $this->actingAsSuperAdmin();

        $this->createQuestion(['title' => 'Existing question', 'code' => 'EX-1']);

        $this->getJson(self::INDEX_PATH.'?search=NoSuchMatchXYZ')
            ->assertOk()
            ->assertJsonPath('data.questions', [])
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_default_per_page_is_ten(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 12; $i++) {
            $this->createQuestion(['title' => "Question {$i}"]);
        }

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonPath('data.pagination.from', 1)
            ->assertJsonPath('data.pagination.to', 10)
            ->assertJsonCount(10, 'data.questions');
    }

    public function test_custom_per_page_and_page_work(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 5; $i++) {
            $this->createQuestion(['title' => "Paged {$i}"]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonPath('data.pagination.from', 3)
            ->assertJsonPath('data.pagination.to', 4)
            ->assertJsonCount(2, 'data.questions');
    }

    public function test_per_page_zero_or_negative_falls_back_to_ten(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 12; $i++) {
            $this->createQuestion(['title' => "Clamp {$i}"]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=0')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonCount(10, 'data.questions');

        $this->getJson(self::INDEX_PATH.'?per_page=-5')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonCount(10, 'data.questions');
    }

    public function test_per_page_above_fifty_is_capped_to_fifty(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 55; $i++) {
            $this->createQuestion(['title' => "Cap {$i}"]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=100')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 50)
            ->assertJsonCount(50, 'data.questions')
            ->assertJsonPath('data.pagination.total', 55)
            ->assertJsonPath('data.pagination.last_page', 2);
    }

    // -------------------------------------------------------------------------
    // Import
    // -------------------------------------------------------------------------

    public function test_import_missing_file_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::IMPORT_PATH, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_import_invalid_mime_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $txt = UploadedFile::fake()->create('questions.txt', 100, 'text/plain');
        $pdf = UploadedFile::fake()->create('questions.pdf', 100, 'application/pdf');

        $this->postImport($txt)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);

        $this->postImport($pdf)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_import_empty_sheet_returns_unprocessable_with_persian_message(): void
    {
        $this->actingAsSuperAdmin();
        Bus::fake();

        Excel::shouldReceive('toArray')
            ->once()
            ->andReturn([[]]);

        $this->postImport($this->fakeXlsx())
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::IMPORT_EMPTY_MESSAGE);

        Bus::assertNotDispatched(ImportChallengeQuestions::class);
    }

    public function test_import_valid_xlsx_dispatches_job_and_returns_accepted(): void
    {
        $this->actingAsSuperAdmin();
        Bus::fake();

        $rows = [
            ['code', 'image', 'title'],
            ['Q-100', 'img.png', 'Imported question'],
        ];

        Excel::shouldReceive('toArray')
            ->once()
            ->andReturn([$rows]);

        $this->postImport($this->fakeXlsx())
            ->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::IMPORT_SUCCESS_MESSAGE);

        Bus::assertDispatched(ImportChallengeQuestions::class);
    }

    public function test_import_valid_csv_dispatches_job_and_returns_accepted(): void
    {
        $this->actingAsSuperAdmin();
        Bus::fake();

        Excel::shouldReceive('toArray')
            ->once()
            ->andReturn([[
                ['code', 'image', 'title'],
                ['Q-CSV', 'img.png', 'CSV question'],
            ]]);

        $csv = UploadedFile::fake()->create('questions.csv', 100, 'text/csv');

        $this->postImport($csv)
            ->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::IMPORT_SUCCESS_MESSAGE);

        Bus::assertDispatched(ImportChallengeQuestions::class);
    }

    public function test_import_excel_exception_returns_server_error_and_logs(): void
    {
        $this->actingAsSuperAdmin();
        Bus::fake();
        Log::spy();

        Excel::shouldReceive('toArray')
            ->once()
            ->andThrow(new \RuntimeException('Unable to parse spreadsheet'));

        $this->postImport($this->fakeXlsx())
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::IMPORT_ERROR_MESSAGE);

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Challenge questions import failed'
                    && isset($context['exception'])
                    && $context['exception'] instanceof \RuntimeException;
            });

        Bus::assertNotDispatched(ImportChallengeQuestions::class);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_question_and_its_answers(): void
    {
        $this->actingAsSuperAdmin();

        $question = $this->createQuestion(['title' => 'To delete']);
        $answerA = $this->createAnswer($question, ['title' => 'A']);
        $answerB = $this->createAnswer($question, ['title' => 'B']);

        $this->deleteJson($this->destroyPath($question))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
        $this->assertDatabaseMissing('answers', ['id' => $answerA->id]);
        $this->assertDatabaseMissing('answers', ['id' => $answerB->id]);
    }

    public function test_destroy_nonexistent_question_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson(self::INDEX_PATH.'/999999')
            ->assertNotFound();
    }

    public function test_destroy_does_not_delete_other_questions_answers(): void
    {
        $this->actingAsSuperAdmin();

        $target = $this->createQuestion(['title' => 'Target']);
        $targetAnswer = $this->createAnswer($target, ['title' => 'Target answer']);

        $other = $this->createQuestion(['title' => 'Other']);
        $otherAnswer = $this->createAnswer($other, ['title' => 'Other answer']);

        $this->deleteJson($this->destroyPath($target))
            ->assertOk();

        $this->assertDatabaseMissing('questions', ['id' => $target->id]);
        $this->assertDatabaseMissing('answers', ['id' => $targetAnswer->id]);
        $this->assertDatabaseHas('questions', ['id' => $other->id]);
        $this->assertDatabaseHas('answers', [
            'id' => $otherAnswer->id,
            'question_id' => $other->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function test_index_question_without_answers_returns_empty_answers_array(): void
    {
        $this->actingAsSuperAdmin();

        $this->createQuestion(['title' => 'No answers yet']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.questions.0.answers', [])
            ->assertJsonCount(0, 'data.questions.0.answers');
    }

    public function test_incorrect_answer_is_correct_is_boolean_false(): void
    {
        $this->actingAsSuperAdmin();

        $question = $this->createQuestion();
        $this->createAnswer($question, ['title' => 'Wrong', 'is_correct' => false]);

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $this->assertFalse($response->json('data.questions.0.answers.0.is_correct'));
        $this->assertIsBool($response->json('data.questions.0.answers.0.is_correct'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function destroyPath(Question $question): string
    {
        return self::INDEX_PATH.'/'.$question->id;
    }

    private function fakeXlsx(): UploadedFile
    {
        return UploadedFile::fake()->create(
            'questions.xlsx',
            100,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    private function postImport(UploadedFile $file)
    {
        return $this->post(self::IMPORT_PATH, [
            'file' => $file,
        ], [
            'Accept' => 'application/json',
        ]);
    }

    private function createQuestion(array $overrides = []): Question
    {
        return Question::create(array_merge([
            'code' => 'Q-'.Str::upper(Str::random(8)),
            'title' => 'Challenge question '.Str::random(5),
            'image' => 'questions/default.png',
            'creator_code' => (string) random_int(1000, 9999),
            'prize' => 1000,
        ], $overrides));
    }

    private function createAnswer(Question $question, array $overrides = []): Answer
    {
        return Answer::create(array_merge([
            'question_id' => $question->id,
            'title' => 'Answer '.Str::random(5),
            'image' => 'answers/default.png',
            'is_correct' => false,
        ], $overrides));
    }
}
