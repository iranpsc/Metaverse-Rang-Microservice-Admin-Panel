<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Challenge\ImportChallengeQuestionsRequest;
use App\Http\Resources\Challenge\QuestionResource;
use App\Imports\QuestionFileImport;
use App\Jobs\ImportChallengeQuestions;
use App\Models\Challenge\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ChallengeQuestionsController extends Controller
{
    /**
     * Display a listing of the challenge questions.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 50) : 10;
        $search = $request->get('search');

        $query = Question::query()
            ->with(['answers' => static fn ($query) => $query->orderBy('created_at')])
            ->orderBy('id');

        if ($search) {
            $query->where(static function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $questions = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'success' => true,
            'data' => [
                'questions' => QuestionResource::collection($questions->items())->resolve($request),
                'pagination' => [
                    'current_page' => $questions->currentPage(),
                    'last_page' => $questions->lastPage(),
                    'per_page' => $questions->perPage(),
                    'total' => $questions->total(),
                    'from' => $questions->firstItem(),
                    'to' => $questions->lastItem(),
                ],
            ],
            'message' => 'سوالات چالش با موفقیت دریافت شد.',
        ]);
    }

    /**
     * Import questions from an uploaded file.
     */
    public function import(ImportChallengeQuestionsRequest $request): JsonResponse
    {
        try {
            $uploadedFile = $request->file('file');
            $sheets = Excel::toArray(new QuestionFileImport(), $uploadedFile);
            $data = $sheets[0] ?? [];

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'فایل انتخاب شده حاوی داده‌ای نیست.',
                ], 422);
            }

            ImportChallengeQuestions::dispatch($data);

            return response()->json([
                'success' => true,
                'message' => 'درون‌ریزی سوالات در صف پردازش قرار گرفت.',
            ], 202);
        } catch (\Throwable $exception) {
            Log::error('Challenge questions import failed', [
                'exception' => $exception,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'بروز خطا در پردازش فایل درون‌ریزی.',
            ], 500);
        }
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy(Question $question): JsonResponse
    {
        DB::transaction(static function () use ($question) {
            $question->answers()->delete();
            $question->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'سوال با موفقیت حذف شد.',
        ]);
    }
}


