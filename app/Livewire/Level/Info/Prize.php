<?php

namespace App\Livewire\Level\Info;

use App\Models\Level\Level;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Prize extends Component
{
    use SendsVerificationSms;

    public Level $level;

    public $prize, $psc, $yellow, $blue, $red, $effect, $satisfaction;

    public function mount()
    {
        $this->prize = $this->level->prize;
        $this->psc = $this->prize ? $this->prize->psc : 0;
        $this->yellow = $this->prize ? $this->prize->yellow : 0;
        $this->blue = $this->prize ? $this->prize->blue : 0;
        $this->red = $this->prize ? $this->prize->red : 0;
        $this->satisfaction = $this->prize ? $this->prize->satisfaction : 0;
        $this->effect = $this->prize ? $this->prize->effect : 0;
        $this->admin = Auth::guard('admin')->user();
    }

    protected $rules = [
        'psc' => 'required|integer|min:0',
        'yellow' => 'required|integer|min:0',
        'blue' => 'required|integer|min:0',
        'red' => 'required|integer|min:0',
        'satisfaction' => 'required|decimal:0,4|min:0',
        'effect' => 'required|integer|min:0',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function save()
    {
        $data = $this->validate();

        unset($data['phone_verification']);
        unset($data['access_password']);

        if ($this->prize) {
            $this->prize->update($data);
        } else {
            $this->prize = $this->level->prize()->create($data);
        }

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }

    public function render()
    {
        return view('livewire.level.info.prize');
    }
}
