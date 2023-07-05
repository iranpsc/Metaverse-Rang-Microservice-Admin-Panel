<?php

namespace App\Http\Livewire\Videos;

use Livewire\Component;
use App\Traits\SendsVerificationSms;
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
        $this->admin = auth()->guard('admin')->user();
        $this->title = $this->videoDb->title;
        $this->description = $this->videoDb->description;
    }

    public function save()
    {
        $this->validate();

        if ($this->image) {
            $imageUrl = $this->image->store('tutorials/' . $this->videoDb->categoriable->slug, 'public');
        }

        if ($this->video) {
            $videoUrl = $this->video->store('tutorials/' . $this->videoDb->categoriable->slug, 'public');
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
