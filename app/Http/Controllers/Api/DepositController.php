<?php

namespace App\Http\Controllers\Api;

use App\Exports\PaymentsExport;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    /**
     * Get paginated Deposit/Payment records with search
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->get('search', '');
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $query = Payment::with('user:id,name');

        if ($searchTerm) {
            $query->search($searchTerm);
        }

        $payments = $query->paginate($perPage, ['*'], 'page', $page);

        $formattedPayments = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'user_id' => $payment->user_id,
                'user_name' => $payment->user->name ?? '-',
                'amount' => number_format($payment->amount ?? 0),
                'ref_id' => $payment->ref_id ?? '-',
                'card_pan' => $payment->card_pan ?? '-',
                'gateway' => $payment->gateway ?? $payment->gate_way ?? '-',
                'product_title' => $payment->getTitle(),
                'product' => $payment->product,
                'created_at' => $payment->created_at ? jdate($payment->created_at)->format('Y/m/d') : '-',
                'created_at_time' => $payment->created_at ? jdate($payment->created_at)->format('H:i:s') : '-',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $formattedPayments,
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                    'from' => $payments->firstItem(),
                    'to' => $payments->lastItem(),
                ],
            ],
            'message' => 'Deposits retrieved successfully.',
        ]);
    }

    /**
     * Export deposits to Excel
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return (new PaymentsExport)->download('transactions.xlsx');
    }
}

