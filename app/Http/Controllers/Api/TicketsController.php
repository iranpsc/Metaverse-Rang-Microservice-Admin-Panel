<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Notifications\TicketResponded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TicketsController extends Controller
{
    /**
     * Get tickets by department
     */
    public function index(Request $request)
    {
        $department = $request->query('department');
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search');
        $search = $search ? trim($search) : '';

        $query = Ticket::with(['responses', 'sender'])
            ->whereIn('department', (array) $department);

        // Apply search filter if provided
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                  ->orWhere('title', 'like', '%' . $search . '%')
                  ->orWhereHas('sender', function ($senderQuery) use ($search) {
                      $senderQuery->where('name', 'like', '%' . $search . '%')
                                   ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        $query->orderBy('status')
            ->orderBy('importance', 'desc');

        $tickets = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'tickets' => TicketResource::collection($tickets->items()),
                'pagination' => [
                    'total' => $tickets->total(),
                    'per_page' => $tickets->perPage(),
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'from' => $tickets->firstItem(),
                    'to' => $tickets->lastItem(),
                ]
            ]
        ]);
    }

    /**
     * Send response to ticket
     */
    public function sendResponse(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'response' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,png,jpeg,jpg',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = Ticket::findOrFail($id);

        $path = "";
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('/tickets/ticketResponses', 'public');
        }

        $ticket->responses()->create([
            'response' => $request->response,
            'attachment' => $path,
            'responser_name' => Auth::user()->name,
            'responser_id' => Auth::id(),
        ]);

        $ticket->update(['status' => 1]);

        $message = 'به تیکت شما به شماره ' . $ticket->code . ' پاسخ داده شد';

        $ticket->sender->notify(new TicketResponded($message));

        return response()->json([
            'success' => true,
            'message' => 'پاسخ تیکت ارسال شد',
            'data' => [
                'ticket' => new TicketResource($ticket->load('responses'))
            ]
        ]);
    }

    /**
     * Transfer ticket to another department
     */
    public function transfer(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'department' => 'required|string|in:technical_support,citizens_safety,investment,inspection,protection,ztb',
            'importance' => 'required|integer|in:-1,0,1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'department' => $request->department,
            'importance' => $request->importance
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تیکت به واحد مورد نظر ارجاع داده شد',
            'data' => [
                'ticket' => new TicketResource($ticket)
            ]
        ]);
    }

    /**
     * Get available departments
     */
    public function getDepartments()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'departments' => [
                    ['value' => 'technical_support', 'label' => 'پشتیبانی فنی'],
                    ['value' => 'citizens_safety', 'label' => 'امنیت شهروندان'],
                    ['value' => 'investment', 'label' => 'سرمایه گذاری'],
                    ['value' => 'inspection', 'label' => 'بازرسی'],
                    ['value' => 'protection', 'label' => 'حراست'],
                    ['value' => 'ztb', 'label' => 'مدیریت کل ز.ت.ب'],
                ]
            ]
        ]);
    }
}

