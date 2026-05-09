<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Calendar\StoreCalendarRequest;
use App\Http\Requests\Calendar\UpdateCalendarRequest;
use App\Http\Resources\Calendar\CalendarResource;
use App\Models\Calendar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class CalendarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        $query = Calendar::query()
            ->where('is_version', false)
            ->withCount([
                'views',
                'interactions as likes_count' => fn ($q) => $q->where('liked', 1),
                'interactions as dislikes_count' => fn ($q) => $q->where('liked', 0),
            ])
            ->orderByDesc('starts_at');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $events = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'events' => CalendarResource::collection($events->items()),
                'pagination' => [
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage(),
                    'per_page' => $events->perPage(),
                    'total' => $events->total(),
                    'from' => $events->firstItem(),
                    'to' => $events->lastItem(),
                ],
            ],
            'message' => 'رویدادها با موفقیت بازیابی شدند.',
        ]);
    }

    public function store(StoreCalendarRequest $request): JsonResponse
    {
        $admin = Auth::guard('admin')->user();

        $startsAt = Jalalian::fromFormat('Y/m/d', $request->input('start_date'))
            ->toCarbon()
            ->setTimeFromTimeString($request->input('start_time'));

        $endsAt = Jalalian::fromFormat('Y/m/d', $request->input('end_date'))
            ->toCarbon()
            ->setTimeFromTimeString($request->input('end_time'));

        $imagePath = $request->file('image')->store('calendars', 'public');

        $calendar = Calendar::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'color' => $request->input('color') ?: '#000000',
            'writer' => $admin?->name,
            'btn_name' => $request->input('btn_name'),
            'btn_link' => $request->input('btn_link'),
            'image' => url('uploads/' . $imagePath),
        ]);

        $calendar->loadCount([
            'views',
            'interactions as likes_count' => fn ($q) => $q->where('liked', 1),
            'interactions as dislikes_count' => fn ($q) => $q->where('liked', 0),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'event' => new CalendarResource($calendar),
            ],
            'message' => 'وقعه ثبت شد.',
        ], 201);
    }

    public function update(UpdateCalendarRequest $request, Calendar $calendar): JsonResponse
    {
        abort_if($calendar->is_version, 403, 'امکان ویرایش این رویداد وجود ندارد.');

        $startsAt = Jalalian::fromFormat('Y/m/d', $request->input('start_date'))
            ->toCarbon()
            ->setTimeFromTimeString($request->input('start_time'));

        $endsAt = null;

        if ($request->filled('end_date') && $request->filled('end_time')) {
            $endsAt = Jalalian::fromFormat('Y/m/d', $request->input('end_date'))
                ->toCarbon()
                ->setTimeFromTimeString($request->input('end_time'));
        }

        $data = [
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'color' => $request->input('color') ?: '#000000',
            'btn_name' => $request->input('btn_name'),
            'btn_link' => $request->input('btn_link'),
        ];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('calendars', 'public');
            $data['image'] = url('uploads/' . $imagePath);
        }

        $calendar->update($data);

        $calendar->loadCount([
            'views',
            'interactions as likes_count' => fn ($q) => $q->where('liked', 1),
            'interactions as dislikes_count' => fn ($q) => $q->where('liked', 0),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'event' => new CalendarResource($calendar),
            ],
            'message' => 'وقعه ویرایش شد.',
        ]);
    }

    public function destroy(Calendar $calendar): JsonResponse
    {
        abort_if($calendar->is_version, 403, 'امکان حذف این رویداد وجود ندارد.');

        $calendar->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'وقعه حذف شد.',
        ]);
    }
}


