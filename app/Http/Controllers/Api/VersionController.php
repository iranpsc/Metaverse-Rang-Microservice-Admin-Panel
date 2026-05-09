<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Version\StoreVersionRequest;
use App\Http\Resources\Calendar\CalendarResource;
use App\Models\Calendar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class VersionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search');

        $query = Calendar::query()
            ->version()
            ->withCount('views')
            ->orderByDesc('id');

        if (!empty($search)) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('version_title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $versions = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'versions' => CalendarResource::collection($versions->items()),
                'pagination' => [
                    'current_page' => $versions->currentPage(),
                    'last_page' => $versions->lastPage(),
                    'per_page' => $versions->perPage(),
                    'total' => $versions->total(),
                    'from' => $versions->firstItem(),
                    'to' => $versions->lastItem(),
                ],
            ],
            'message' => 'ورژن‌ها با موفقیت بازیابی شدند.',
        ]);
    }

    public function store(StoreVersionRequest $request): JsonResponse
    {
        $admin = Auth::guard('admin')->user();

        $startsAt = Jalalian::fromFormat('Y/m/d', $request->input('starts_at'))
            ->toCarbon()
            ->startOfDay();

        $version = Calendar::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'version_title' => $request->input('version_title'),
            'starts_at' => $startsAt,
            'is_version' => true,
            'writer' => $admin?->name,
        ]);

        $version->loadCount('views');

        return response()->json([
            'success' => true,
            'data' => [
                'version' => new CalendarResource($version),
            ],
            'message' => 'ورژن جدید با موفقیت ایجاد شد.',
        ], 201);
    }

    public function destroy(Calendar $version): JsonResponse
    {
        abort_if(!$version->is_version, 403, 'امکان حذف این ورژن وجود ندارد.');

        $version->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'ورژن با موفقیت حذف شد.',
        ]);
    }
}


