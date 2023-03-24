<?php

namespace App\Http\Livewire\Lands;

use App\Models\FeatureLimit;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use App\Helpers\SMS;
use App\Models\FeatureProperties;
use function App\Helpers\convertDateToCarbon;

class Limits extends Component
{
    public $title, $startingId, $endingId,
        $verifiedKycLimit = false,
        $verifiedBankAccountLimit = false,
        $notAllowedToBeSold = false,
        $under18BuyLimit = false,
        $moreThan18BuyLimit = false,
        $dynastyOwnerBuyLimit = false,
        $buyCountLimitForEachIndividual,
        $price,
        $startingDate,
        $endingDate,
        $code,
        $accessPassword,
        $admin;

    protected $listeners = [
        'deleteFeatureLimit' => 'delete',
        'limitCreated' => '$refresh',
        'limitUpdated' => '$refresh',
        'limitDeleted' => '$refresh'
    ];

    protected $rules = [
        'title' => 'required|string',
        'startingId' => 'required|string|exists:feature_properties,id',
        'endingId' => 'required|string|exists:feature_properties,id',
        'verifiedKycLimit' => 'nullable|boolean',
        'verifiedBankAccountLimit' => 'nullable|boolean',
        'notAllowedToBeSold' => 'nullable|boolean',
        'under18BuyLimit' => 'nullable|boolean',
        'moreThan18BuyLimit' => 'nullable|boolean',
        'dynastyOwnerBuyLimit' => 'nullable|boolean',
        'buyCountLimitForEachIndividual' => 'nullable|integer|min:0',
        'price' => 'nullable|integer|min:0',
        'startingDate' => 'required|shamsi_date',
        'endingDate' => 'required|shamsi_date',
        'code' => 'required|integer',
        'accessPassword' => 'required',
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function sendSMS()
    {
        $verify_code = random_int(100000, 999999);
        Cache::put('feature.limits.verify.code.' . $this->admin->id, Hash::make($verify_code), now()->addMinutes(5));
        $result = SMS::send($this->admin->phone, $verify_code);
        if (is_array($result)) {
            foreach ($result as $r) {
                session()->flash('success', $r->statustext);
            }
        } else {
            session()->flash('error', explode(":", $result)[1]);
            Cache::forget('feature.limits.verify.code.' . $this->admin->id);
        }
    }

    public function save()
    {
        $this->validate();

        if (!Hash::check($this->code, Cache::get('feature.limits.verify.code.' . $this->admin->id))) {
            $this->addError('code', 'کد تایید وارد شده صحیح نمی باشد');
        } else if (!Hash::check($this->accessPassword, $this->admin->access_password)) {
            $this->addError('accessPassword', 'رمز دسترسی صحیح نمی باشد');
        } else {
            $date_st = \Morilog\Jalali\CalendarUtils::convertNumbers($this->startingDate, true);
            $date_ed = \Morilog\Jalali\CalendarUtils::convertNumbers($this->endingDate, true);
            if ($this->endingId < $this->startingId) {
                $this->addError('endingId', 'مقدار نا معتبر است.');
            } elseif ($date_st >= $date_ed) {
                $this->addError('endingDate', 'مقدار نا معتبر است.');
            } else {
                $limit = new FeatureLimit();
                $limit->title = $this->title;
                $limit->start_id = $this->startingId;
                $limit->end_id = $this->endingId;
                $limit->verified_kyc_limit = $this->verifiedKycLimit;
                $limit->verified_bank_account_limit = $this->verifiedBankAccountLimit;
                $limit->not_sellable = $this->notAllowedToBeSold;
                $limit->under_18_limit = $this->under18BuyLimit;
                $limit->more_than_18_limit = $this->moreThan18BuyLimit;
                $limit->dynasty_owner_limit = $this->dynastyOwnerBuyLimit;
                $limit->individual_buy_limit = $this->buyCountLimitForEachIndividual ?? 0;
                $limit->price = intval($this->price);
                $limit->start_date = convertDateToCarbon($this->startingDate);
                $limit->end_date = convertDateToCarbon($this->endingDate);
                $limit->save();

                if ($this->notAllowedToBeSold) {
                    FeatureProperties::where('id', '>=', $this->startingId)
                        ->where('id', '<=', $this->endingId)
                        ->orderBy('id', 'asc')
                        ->each(function ($feature) {
                            if ($feature->owner === 'rgb') {
                                if ($feature->karbari === 'm') {
                                    $feature->update([
                                        'rgb' => 'f',
                                        'label' => 'locked',
                                    ]);
                                } elseif ($feature->karbari === 't') {
                                    $feature->update([
                                        'rgb' => 'm',
                                        'label' => 'locked',
                                    ]);
                                }
                                if ($feature->karbari === 'a') {
                                    $feature->update([
                                        'rgb' => 'tt',
                                        'label' => 'locked',
                                    ]);
                                }

                                if ($this->price >= 0) {
                                    $feature->update(['stability' => intval(trim($this->price))]);
                                }
                            }
                        });
                } else {
                    FeatureProperties::where('id', '>=', $this->startingId)
                        ->where('id', '<=', $this->endingId)
                        ->orderBy('id', 'asc')
                        ->each(function ($feature) {
                            if ($feature->owner === 'rgb') {
                                if ($feature->karbari === 'm') {
                                    $feature->update([
                                        'rgb' => 'g',
                                        'label' => ''
                                    ]);
                                } elseif ($feature->karbari === 't') {
                                    $feature->update([
                                        'rgb' => 'n',
                                        'label' => ''
                                    ]);
                                }
                                if ($feature->karbari === 'a') {
                                    $feature->update([
                                        'rgb' => 'uu',
                                        'label' => ''
                                    ]);
                                }
                            }
                        });
                }
                $this->resetExcept('admin');
                Cache::forget('feature.limits.verify.code.' . $this->admin->id);
                session()->flash('success', 'محدودیت وارد شد');
                $this->emitSelf('limitCreated');
            }
        }
    }

    public function updatedUnder18BuyLimit()
    {
        $this->verifiedKycLimit = true;
    }

    public function updatedMoreThan18BuyLimit()
    {
        $this->verifiedKycLimit = true;
    }

    public function delete(FeatureLimit $featureLimit)
    {
        FeatureProperties::where('id', '>=', $featureLimit->start_id)
            ->orderBy('id', 'asc')
            ->where('id', '<=', $featureLimit->end_id)
            ->each(function ($feature) {
                if ($feature->owner === 'rgb') {
                    if ($feature->karbari === 'm') {
                        $feature->update([
                            'rgb' => 'a',
                            'label' => '',
                            'stability' => $feature->area * $feature->density
                        ]);
                    } elseif ($feature->karbari === 't') {
                        $feature->update([
                            'rgb' => 'h',
                            'label' => '',
                            'stability' => $feature->area * $feature->density
                        ]);
                    }
                    if ($feature->karbari === 'a') {
                        $feature->update([
                            'rgb' => 'o',
                            'label' => '',
                            'stability' => $feature->area * $feature->density
                        ]);
                    }
                }
            });
        $featureLimit->delete();
        $this->emitSelf('limitDeleted');
    }

    public function render()
    {
        return view('livewire.lands.limits', [
            'limits' => FeatureLimit::all(),
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
