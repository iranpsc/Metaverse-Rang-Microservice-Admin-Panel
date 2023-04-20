<?php

namespace App\Http\Livewire\Maps;

use App\Jobs\ImportMaps;
use App\Models\Map;
use App\Traits\SendsVerificationSms;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class InsertIntoDatabase extends Component
{
    use SendsVerificationSms;

    public $map;

    protected $rules = [
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function insertIntoDatabase(Map $map)
    {
        $this->validate();

        if ($map->status) {
            session()->flash('error', 'اطلاعات قبلا وارد دیتابیس شده است');
            return;
        }

        ImportMaps::dispatch($map);
        $map->update(['status' => 1]);
        session()->flash('success', 'اطلاعات با موفقیت وارد دیتابیس شد');
        $this->reset('code', 'accessPassword');
        $this->emitUp('mapsInsertedToDatabase');
    }

    public function render()
    {
        return view('livewire.maps.insert-into-database');
    }
}
