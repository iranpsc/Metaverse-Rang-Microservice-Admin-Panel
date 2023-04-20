<?php

namespace App\Http\Livewire\Level;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\VerifiesPhoneAndAccessPassword;
use Illuminate\Support\Facades\Auth;

class Update extends Component
{
    use WithFileUploads, VerifiesPhoneAndAccessPassword;

    public $name, $score, $level, $image, $backgroundImage;

    public function mount()
    {
        $this->name = $this->level->name;
        $this->score = $this->level->score;
        $this->admin = Auth::guard('admin')->user();
    }

    protected $rules = [
        'name' => 'required|string',
        'image' => 'nullable|image|mimes:jpg,png,bmp,jpeg',
        'score' => 'required|integer',
        'backgroundImage' => 'nullable|image|max:5024',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function save()
    {
        $this->validate();

        $backgroundImageUrl = $this->backgroundImage
            ? url('uploads/' . $this->backgroundImage->store('levels', 'public'))
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

        session()->flash('success', 'سطح ویرایش شد');
        $this->emitUp('levelUpdated');
    }

    public function render()
    {
        return view('livewire.level.update');
    }
}
