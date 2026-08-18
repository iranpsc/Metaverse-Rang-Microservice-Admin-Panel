<?php

namespace App\Livewire\Level;

use App\Traits\SendsVerificationSms;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Update extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public $name, $slug, $score, $level, $image, $background_image;

    public function mount()
    {
        $this->name = $this->level->name;
        $this->slug = $this->level->slug;
        $this->score = $this->level->score;
        $this->admin = Auth::guard('admin')->user();
    }

    protected $rules = [
        'name' => 'required|string',
        'image' => 'nullable|image|mimes:jpg,png,bmp,jpeg|max:1024',
        'score' => 'required|integer',
        'background_image' => 'nullable|image|max:1024',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function save()
    {
        $this->validate();

        $backgroundImageUrl = $this->background_image
            ? url('uploads/' . $this->background_image->store('levels', 'public'))
            : $this->level->background_image;

        $this->level->update([
            'name' => $this->name,
            'score' => $this->score,
            'background_image' => $backgroundImageUrl
        ]);

        if ($this->image) {
            $url = $this->image->store('levels', 'public');
            if ($this->level->image) {
                $this->level->image->update(['url' => $url]);
            } else {
                $this->level->image()->create(['url' => $url]);
            }
        }

        $this->dispatch('notify', message: 'سطح ویرایش شد');
    }
}
