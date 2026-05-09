<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SystemVariable\StoreSystemVariableRequest;
use App\Http\Requests\SystemVariable\UpdateSystemVariableRequest;
use App\Http\Resources\SystemVariable\SystemVariableResource;
use App\Models\SystemVariable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SystemVariablesController extends Controller
{
    /**
     * Display a listing of the system variables.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;
        $search = $request->get('search');

        $query = SystemVariable::query()
            ->with(['changeLogs' => static fn ($query) => $query->orderByDesc('created_at')])
            ->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        $variables = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'success' => true,
            'data' => [
                'variables' => SystemVariableResource::collection($variables->items())->resolve($request),
                'pagination' => [
                    'current_page' => $variables->currentPage(),
                    'last_page' => $variables->lastPage(),
                    'per_page' => $variables->perPage(),
                    'total' => $variables->total(),
                    'from' => $variables->firstItem(),
                    'to' => $variables->lastItem(),
                ],
            ],
            'message' => 'System variables retrieved successfully.',
        ]);
    }

    /**
     * Store a newly created system variable in storage.
     */
    public function store(StoreSystemVariableRequest $request): JsonResponse
    {
        $data = $request->validated();

        $variable = SystemVariable::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'value' => $data['value'],
        ]);

        $variable->load(['changeLogs' => static fn ($query) => $query->orderByDesc('created_at')]);

        return response()->json([
            'success' => true,
            'data' => (new SystemVariableResource($variable))->resolve($request),
            'message' => 'متغیر سیستم با موفقیت ایجاد شد.',
        ], 201);
    }

    /**
     * Update the specified system variable in storage.
     */
    public function update(UpdateSystemVariableRequest $request, SystemVariable $systemVariable): JsonResponse
    {
        $data = $request->validated();

        DB::transaction(static function () use ($systemVariable, $data) {
            $admin = Auth::guard('admin')->user();

            $systemVariable->changeLogs()->create([
                'changer_name' => $admin?->name,
                'previous_value' => $systemVariable->value,
                'current_value' => $data['value'],
                'note' => $data['note'] ?? null,
            ]);

            $systemVariable->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'value' => $data['value'],
            ]);
        });

        $systemVariable->load(['changeLogs' => static fn ($query) => $query->orderByDesc('created_at')]);

        return response()->json([
            'success' => true,
            'data' => (new SystemVariableResource($systemVariable))->resolve($request),
            'message' => 'متغیر سیستم با موفقیت به روز شد.',
        ]);
    }

    /**
     * Remove the specified system variable from storage.
     */
    public function destroy(SystemVariable $systemVariable): JsonResponse
    {
        DB::transaction(static function () use ($systemVariable) {
            $systemVariable->changeLogs()->delete();
            $systemVariable->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'متغیر سیستم با موفقیت حذف شد.',
        ]);
    }
}


