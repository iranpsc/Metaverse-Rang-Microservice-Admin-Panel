<?php

namespace App\Livewire\Citizens;

use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Illuminate\Validation\Rule as ValidationRule;

class KycVideoText extends Component
{
    use SendsVerificationSms;

    public $text;

    protected function rules()
    {
        return [
            'text' => 'required|string',
            'phone_verification' => [
                'nullable',
                ValidationRule::requiredIf(app()->environment('production')),
                'is_valid_verify_code'
            ],
            'access_password' => [
                'nullable',
                ValidationRule::requiredIf(app()->environment('production')),
                'is_valid_access_password'
            ],
        ];
    }

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function save()
    {
        $this->validate();

        $file = storage_path('app/public/kyc_video_text.json');

        // Check if the file exists
        if (file_exists($file)) {
            // Read the existing JSON data from the file
            $jsonData = file_get_contents($file);

            // Decode the JSON data into an associative array
            $existingData = json_decode($jsonData, true);

            if ($existingData === null) {
                // If the existing data is not valid JSON, initialize with an empty array
                $existingData = ['texts' => []];
            }
        } else {
            // If the file doesn't exist, create a new JSON file with the initial data
            $existingData = ['texts' => []];
        }

        // Append the new data to the existing data
        $existingData['texts'][] = $this->text;

        // Encode the updated data back to JSON
        $updatedJsonData = json_encode($existingData);

        // Write the updated JSON data back to the file
        file_put_contents($file, $updatedJsonData);

        $this->dispatch('notify', message: 'متن احراز ویدیویی با موفقیت ثبت شد.', type: 'success');
        $this->reset('text');
    }

    public function render()
    {
        return view('livewire.citizens.kyc-video-text');
    }
}
