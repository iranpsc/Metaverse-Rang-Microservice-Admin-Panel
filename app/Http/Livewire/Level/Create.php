<?php

namespace App\Http\Livewire\Level;

use Livewire\Component;
use App\Models\Level\Level;
use Livewire\WithFileUploads;
use App\Traits\VerifiesPhoneAndAccessPassword;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    use WithFileUploads, VerifiesPhoneAndAccessPassword;

    public $name, $score, $slug, $image, $backgroundImage;

    protected $rules = [
        'name' => 'required|string|unique:levels',
        'image' => 'nullable|image|mimes:jpg,png,bmp,jpeg',
        'slug' => 'required|string|unique:levels',
        'score' => 'required|min:0|integer',
        'backgroundImage' => 'required|image|max:5024',
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

        $backgroundImageUrl = url('uploads/' . $this->backgroundImage->store('levels', 'public'));

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

        session()->flash('success', 'سطح ایجاد شد');
        $this->reset('name', 'slug', 'score');
        $this->emitUp('levelCreated');
    }

    public function render()
    {
        return view('livewire.level.create');
    }
}
