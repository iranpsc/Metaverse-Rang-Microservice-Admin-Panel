<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Models\Dynasty\Dynasty;
use App\Models\Referral;
use App\Models\ReferralOrderHistory;
use App\Models\Variable;
use App\Repositories\FeatureRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private UserRepository $userRepository,
        private FeatureRepository $featureRepository,
        private OrderRepository $orderRepository
    ) {
    }

    /**
     * Get dashboard statistics
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $referralAmount = ReferralOrderHistory::sum('amount') * Variable::getRate('psc');
        $depositedRialAmount = $this->orderRepository->totalOrderAmount();

        $data = [
            'users' => [
                'all' => $this->userRepository->allUsers(),
                'verified' => $this->userRepository->verifiedEmailUsers(),
                'verified_phone' => $this->userRepository->verifiedPhoneUsers(),
                'kyc_verified' => $this->userRepository->verifiedKycUsers(),
            ],
            'dynasties' => Dynasty::count(),
            'features' => [
                'all' => $this->featureRepository->all(),
                'sold' => $this->featureRepository->sold(),
            ],
            'referrals' => Referral::count(),
            'referral_amount' => (float) $referralAmount,
            'sold_assets' => [
                'psc' => (float) $this->orderRepository->pscOrderAmount(),
                'red' => (float) $this->orderRepository->redOrderAmount(),
                'blue' => (float) $this->orderRepository->blueOrderAmount(),
                'yellow' => (float) $this->orderRepository->yellowOrderAmount(),
            ],
            'deposited_rial_amount' => (float) $depositedRialAmount,
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Dashboard statistics retrieved successfully.',
        ]);
    }
}

