<?php

namespace App\Livewire\Videos;

use App\Models\Video;
use Livewire\Component;
use App\Traits\SendsVerificationSms;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class EditVideo extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public Video $videoDb;

    public $title, $description, $image, $video;

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:20000',
            'image' => 'nullable|image|max:1024',
            'video' => 'nullable|string',
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_verify_code'
            ],
            'access_password' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_access_password'
            ],
        ];
    }

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
            $imageUrl = $this->image->store('tutorials/' . $this->videoDb->category->slug, 'public');
        }

        if ($this->video) {
            $videoUrl = 'tutorials/' . $this->videoDb->category->category->slug . '/' . $this->videoDb->category->slug . '/' . $this->video;

            if (file_exists(storage_path('app/resumable-tmp/' . $this->video))) {
                rename(storage_path('app/resumable-tmp/' . $this->video), storage_path('app/public/' . $videoUrl));
            } else {
                $this->addError('video', 'فایل ویدیو را بارگذاری کنید.');
                return;
            }
        }

        $this->videoDb->update([
            'title' => $this->title,
            'description' => $this->description,
            'fileName' => $videoUrl ?? $this->videoDb->fileName,
            'image' => $imageUrl ?? $this->videoDb->image,
        ]);

        $this->dispatch('notify', message: 'ویدیو بروزرسانی شد');
    }

    public function render()
    {
        return view('livewire.videos.edit-video');
    }
}
