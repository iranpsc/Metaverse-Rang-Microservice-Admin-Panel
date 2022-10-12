<?php

namespace App\Http\Livewire\Citizens\Kyc;

use App\Models\KycError;
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

    public $errors = [];

    public function save_errors($input)
    {

        switch ($input) {
            case 'fname_err':
                array_push($this->errors, ['fname_err' => $this->fname_err]);
                break;
            case 'lname_err':
                array_push($this->errors, ['lname_err' => $this->lname_err]);
                break;
            case 'father_name_err':
                array_push($this->errors, ['father_name_err' => $this->father_name_err]);
                break;
            case 'melli_code_err':
                array_push($this->errors, ['melli_code_err' => $this->melli_code_err]);
                break;
            case 'province_err':
                array_push($this->errors, ['province_err' => $this->province_err]);
                break;
            case 'city_err':
                array_push($this->errors, ['city_err' => $this->city_err]);
                break;
            case 'number_err':
                array_push($this->errors, ['number_err' => $this->number_err]);
                break;
            case 'postal_code_err':
                array_push($this->errors, ['postal_code_err' => $this->postal_code_err]);
                break;
            case 'address_err':
                array_push($this->errors, ['address_err' => $this->address_err]);
                break;
            case 'melli_card_err':
                array_push($this->errors, ['melli_card_err' => $this->melli_card_err]);
                break;
            case 'prove_picture_err':
                array_push($this->errors, ['prove_picture_err' => $this->prove_picture_err]);
                break;
            case 'resume_err':
                array_push($this->errors, ['resume_err' => $this->resume_err]);
                break;
        }
    }


    public function save()
    {
        if (!empty($this->errors)) {

            $kycErr = KycError::firstOrCreate(['kyc_id' => $this->kyc->id]);

            for ($i = 0; $i < count($this->errors); $i++) {
                $arr = $this->errors[$i];
                foreach ($arr as $key => $value) {
                    $kycErr->update([
                        $key => $value,
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
