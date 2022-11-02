<?php

namespace App\Http\Controllers;

use App\Models\Dynasty\Dynasty;
use App\Models\Referral;
use App\Models\ReferralOrderHistory;
use App\Models\Variable;
use App\Repositories\FeatureRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;

class DashboardController extends Controller
{
    private $userRepository;
    private $featureRepository;
    private $orderRepository;

    public function __construct(
        UserRepository $userRepository,
        FeatureRepository $featureRepository,
        OrderRepository $orderRepository
    ) {
        $this->userRepository = $userRepository;
        $this->featureRepository = $featureRepository;
        $this->orderRepository = $orderRepository;
    }

    public function index()
    {
        return view('dashboard', [
            'users' => [
                'all' => $this->userRepository->allUsers(),
                'verified' => $this->userRepository->verifiedEmailUsers(),
                'verified-phone' => $this->userRepository->verifiedPhoneUsers(),
                'kyc-verified' => $this->userRepository->verifiedKycUsers(),
            ],
            'dynasties' => Dynasty::count(),
            'features' => [
                'sold' => $this->featureRepository->sold()->count(),
            ],
            'referrals' => Referral::count(),
            'referral_amount' => ReferralOrderHistory::sum('amount')  * Variable::getRate('psc'),
            'sold_assets' => [
                'psc' => $this->orderRepository->pscOrderAmount() ?? 0,
                'red' => $this->orderRepository->redOrderAmount() ?? 0,
                'blue' => $this->orderRepository->blueOrderAmount() ?? 0,
                'yellow' => $this->orderRepository->yellowOrderAmount() ?? 0
            ],
            'deposited_rial_amount' => $this->orderRepository->totalOrderAmount() ?? 0
        ]);
    }
}
