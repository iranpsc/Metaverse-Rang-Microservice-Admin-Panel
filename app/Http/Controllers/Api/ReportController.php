<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    /**
     * Supported report subjects.
     */
    public const SUBJECTS = [
        'spellingError',
        'fpsError',
        'disrespect',
        'displayError',
        'codingError',
    ];

    /**
     * Display a listing of reports filtered by subject/search.
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject' => ['nullable', Rule::in(self::SUBJECTS)],
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $validated = $validator->validate();

        $subject = $validated['subject'] ?? self::SUBJECTS[0];
        $search = $validated['search'] ?? null;
        $perPage = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;

        $query = Report::query()
            ->with(['user', 'images'])
            ->orderByDesc('created_at');

        if ($subject) {
            $query->where('subject', $subject);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }

                $q->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            });
        }

        $reports = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'reports' => ReportResource::collection($reports->items()),
                'pagination' => [
                    'current_page' => $reports->currentPage(),
                    'last_page' => $reports->lastPage(),
                    'per_page' => $reports->perPage(),
                    'total' => $reports->total(),
                    'from' => $reports->firstItem(),
                    'to' => $reports->lastItem(),
                ],
                'filters' => [
                    'subject' => $subject,
                    'search' => $search,
                ],
            ],
            'message' => 'گزارش‌ها با موفقیت بازیابی شدند.',
        ]);
    }
}

