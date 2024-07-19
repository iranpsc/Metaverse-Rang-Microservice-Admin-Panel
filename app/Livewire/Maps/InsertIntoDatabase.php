<?php

namespace App\Livewire\Maps;

use App\Jobs\ImportMaps;
use App\Models\Map;
use App\Traits\SendsVerificationSms;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InsertIntoDatabase extends Component
{
    use SendsVerificationSms;

    public Map $map;

    protected function rules()
    {
        return [
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(fn () => app()->environment() === 'production'),
            ],
            'access_password' => [
                'nullable',
                Rule::requiredIf(fn () => app()->environment() === 'production'),
            ]
        ];
    }

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function insertIntoDatabase(Map $map)
    {
        $this->validate();

        if ($map->isPublished()) {
            return;
        }

        ImportMaps::dispatch($map);

        $map->update(['status' => 1]);

        $this->dispatch('notify', message: 'اطلاعات با موفقیت وارد دیتابیس شد');
    }

    public function render()
    {
        return view('livewire.maps.insert-into-database');
    }
}
