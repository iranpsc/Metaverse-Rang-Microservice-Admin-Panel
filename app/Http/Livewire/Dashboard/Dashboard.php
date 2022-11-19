<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Dynasty\Dynasty;
use App\Models\Referral;
use App\Models\ReferralOrderHistory;
use App\Models\Variable;
use App\Repositories\FeatureRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;

class Dashboard extends Component
{
    public $users = [],
        $dynasties,
        $features = [],
        $referrals,
        $referral_amount,
        $sold_assets = [],
        $deposited_rial_amount;
    public function mount(
        UserRepository $userRepository,
        FeatureRepository $featureRepository,
        OrderRepository $orderRepository
    ) {
        $this->users = [
            'all' => $userRepository->allUsers(),
            'verified' => $userRepository->verifiedEmailUsers(),
            'verified-phone' => $userRepository->verifiedPhoneUsers(),
            'kyc-verified' => $userRepository->verifiedKycUsers(),
        ];
        $this->dynasties = Dynasty::count();
        $this->features = [
            'all' => $featureRepository->all()->count(),
            'sold' => $featureRepository->sold()->count()
        ];
        $this->referrals = Referral::count();
        $this->referral_amount = ReferralOrderHistory::sum('amount')  * Variable::getRate('psc');
        $this->sold_assets = [
            'psc' => $orderRepository->pscOrderAmount(),
            'red' => $orderRepository->redOrderAmount(),
            'blue' => $orderRepository->blueOrderAmount(),
            'yellow' => $orderRepository->yellowOrderAmount()
        ];
        $this->deposited_rial_amount = $orderRepository->totalOrderAmount();
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard')
            ->extends('layouts.app')
            ->section('content');
    }
}
