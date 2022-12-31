<?php

namespace App\Http\Livewire\Citizens\Kyc;

use App\Notifications\KycDeniedNotification;
use Livewire\Component;

class Details extends Component
{

    public $kyc;

    public $fname_err;
    public $lname_err;
    public $father_name_err;
    public $melli_code_err;
    public $province_err;
    public $city_err;
    public $number_err;
    public $postal_code_err;
    public $address_err;
    public $melli_card_err;
    public $prove_picture_err;
    public $resume_err;

    public $kyc_errors = [];

    public function save_errors($input)
    {

        switch ($input) {
            case 'fname_err':
                array_push($this->kyc_errors, ['fname' => $this->fname_err]);
                break;
            case 'lname_err':
                array_push($this->kyc_errors, ['lname' => $this->lname_err]);
                break;
            case 'father_name_err':
                array_push($this->kyc_errors, ['father_name' => $this->father_name_err]);
                break;
            case 'melli_code_err':
                array_push($this->kyc_errors, ['melli_code' => $this->melli_code_err]);
                break;
            case 'province_err':
                array_push($this->kyc_errors, ['province' => $this->province_err]);
                break;
            case 'city_err':
                array_push($this->kyc_errors, ['city' => $this->city_err]);
                break;
            case 'number_err':
                array_push($this->kyc_errors, ['number' => $this->number_err]);
                break;
            case 'postal_code_err':
                array_push($this->kyc_errors, ['postal_code' => $this->postal_code_err]);
                break;
            case 'address_err':
                array_push($this->kyc_errors, ['address' => $this->address_err]);
                break;
            case 'melli_card_err':
                array_push($this->kyc_errors, ['melli_card' => $this->melli_card_err]);
                break;
            case 'prove_picture_err':
                array_push($this->kyc_errors, ['prove_picture' => $this->prove_picture_err]);
                break;
            case 'resume_err':
                array_push($this->kyc_errors, ['resume' => $this->resume_err]);
                break;
        }
    }


    public function save()
    {
        if (!empty($this->kyc_errors)) {
            for ($i = 0; $i < count($this->kyc_errors); $i++) {
                $arr = $this->kyc_errors[$i];
                foreach ($arr as $key => $value) {
                    $this->kyc->errors()->create([
                        'key' => $key,
                        'value' => $value
                    ]);
                }
            }
            $this->kyc->update([
                'status' => -1
            ]);

            $user = $this->kyc->user;
            $message = 'احراز هویت شما تایید نشد';
            $user->notify(new KycDeniedNotification($message));
            session()->flash('error', 'احراز هویت تایید نشد');

        } else {
            $this->kyc->update([
                'status' => 1
            ]);

            session()->flash('success', 'احراز هویت تایید شد');
        }
    }

    public function render()
    {
        return view('livewire.citizens.kyc.details');
    }
}
