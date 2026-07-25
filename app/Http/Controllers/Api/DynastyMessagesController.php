<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dynasty\DynastyMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DynastyMessagesController extends Controller
{
    /**
     * Get all dynasty messages
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $messages = DynastyMessage::all();

            $transformedMessages = $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'type' => $message->type,
                    'type_title' => $message->getMessageTitle(),
                    'message' => $message->message,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedMessages,
                'message' => 'پیام‌های سلسله با موفقیت بارگذاری شدند.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری پیام‌های سلسله',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new dynasty message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => [
                'required',
                'in:requester_confirmation_message,reciever_message,reciever_accept_message,requester_accept_message',
                Rule::unique('dynasty_messages', 'type')
            ],
            'content' => 'required|string',
        ]);

        try {
            $message = DynastyMessage::create([
                'type' => $validated['type'],
                'message' => $validated['content'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت ثبت شد',
                'data' => [
                    'id' => $message->id,
                    'type' => $message->type,
                    'type_title' => $message->getMessageTitle(),
                    'message' => $message->message,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت اطلاعات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing dynasty message
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $message = DynastyMessage::find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'پیام یافت نشد',
            ], 404);
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        try {
            $message->update([
                'message' => $validated['content'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت ثبت شد',
                'data' => [
                    'id' => $message->id,
                    'type' => $message->type,
                    'type_title' => $message->getMessageTitle(),
                    'message' => $message->message,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی اطلاعات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a dynasty message
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $message = DynastyMessage::find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'پیام یافت نشد',
            ], 404);
        }

        try {
            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'پیام با موفقیت حذف شد',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف پیام',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

