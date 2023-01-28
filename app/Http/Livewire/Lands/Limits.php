<?php

namespace App\Http\Livewire\Lands;

use Livewire\Component;
use Morilog\Jalali\Jalalian;

class Limits extends Component
{
    public $title, $startingId, $endingId,
    $verifiedKycLimit = false,
    $verifiedBankAccountLimit = false,
    $notAllowedToBeSold = false,
    $under18BuyLimit = false,
    $moreThan18BuyLimit = false,
    $dynastyOwnerBuyLimit = false,
    $preboughtBy,
    $buyCountLimitForEachIndividual,
    $fixedPscPrice,
    $startingDate,
    $endingDate,
    $limits;

    public function mount()
    {
        // $this->limits =
        $this->startingDate = Jalalian::forge(now())->format('Y/m/d');
        $this->endingDate = Jalalian::forge(now())->format('Y/m/d');
    }

    public function render()
    {
        return view('livewire.lands.limits')
        ->extends('layouts.app')
        ->section('content');
    }
}
