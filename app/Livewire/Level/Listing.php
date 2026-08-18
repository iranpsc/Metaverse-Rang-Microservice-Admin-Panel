<?php

namespace App\Livewire\Level;

use Livewire\Component;
use App\Models\Level\Level;
use Livewire\WithPagination;
use App\Traits\SendsVerificationSms;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

class Listing extends Component
{
    use WithFileUploads, SendsVerificationSms, WithPagination;

    public $name, $score, $slug, $image, $background_image;

    protected $rules = [
        'name' => 'required|string|unique:levels',
        'image' => 'nullable|image|mimes:jpg,png,bmp,jpeg',
        'slug' => 'required|string|unique:levels',
        'score' => 'required|min:0|integer',
        'background_image' => 'required|image|max:5024',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function save()
    {
        $this->validate();

        $backgroundImageUrl = url('uploads/' . $this->background_image->store('levels', 'public'));

        $level = Level::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'score' => $this->score,
            'background_image' => $backgroundImageUrl
        ]);

        if ($this->image) {
            $url = $this->image->store('levels', 'public');
            $level->image()->create(['url' => $url]);
        }

        $this->dispatch('notify', message: 'سطح ایجاد شد');
        $this->reset('name', 'slug', 'score');
    }

    public function delete(Level $level)
    {
        $level->prize?->delete();

        if ($level->image) {
            unlink(public_path('uploads/' . $level->image->url));
            $level->image->delete();
        }

        $level->delete();
    }

    #[Title('مدیریت سطوح')]
    public function render()
    {
        return view('livewire.level.listing', [
            'levels' => Level::with(['prize', 'image', 'generalInfo'])->paginate(10)
        ]);
    }
}
