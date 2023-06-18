<?php

namespace App\Http\Livewire\Videos;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class EditVideo extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public $videoDb, $title, $description, $image, $video;

    protected $rules = [
        'title' => 'required',
        'description' => 'required|string|max:2000',
        'image' => 'nullable|image|max:1024',
        'video' => 'nullable|file|mimes:mp4',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
        $this->title = $this->videoDb->title;
        $this->description = $this->videoDb->description;
    }

    public function save()
    {
        $this->validate();

        if ($this->image) {
            $imageName = implode('.', [Str::random(10), $this->image->getClientOriginalExtension()]);
            $imageUrl = url($this->image->storePubliclyAs('uploads/tutorials/' . $this->videoDb->categoriable->slug, $imageName, 'public'));
        }

        if ($this->video) {
            $videoName = implode('.', [Str::random(10), $this->video->getClientOriginalExtension()]);
            $videoUrl = url($this->video->storePubliclyAs('uploads/tutorials/' . $this->videoDb->categoriable->slug, $videoName, 'public'));
        }

        $this->videoDb->update([
            'title' => $this->title,
            'description' => $this->description,
            'fileName' => $videoUrl ?? $this->videoDb->fileName,
            'image' => $imageUrl ?? $this->videoDb->image,
        ]);

        $this->dispatchBrowserEvent('resourceModified', ['message' => 'ویدیو بروزرسانی شد']);
        $this->emitUp('videoUdated');
    }

    public function render()
    {
        return view('livewire.videos.edit-video');
    }
}
