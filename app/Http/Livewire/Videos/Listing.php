<?php

namespace App\Http\Livewire\Videos;

use App\Models\Video;
use App\Models\VideoCategory;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\VideoSubCategory;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Listing extends Component
{
    use WithPagination, WithFileUploads, SendsVerificationSms;

    public $title, $description, $category, $subCategory, $image, $video, $creator_code;

    private $videos, $videoCategories;

    public $videoSubCategories;

    protected $listeners = [
        'videoCreated' => '$refresh',
        'videoUpdated' => '$refresh',
        'videoDeleted' => '$refresh',
        'deleteTrainingVideo' => 'deleteVideo',
    ];

    public function mount()
    {
        $this->admin = Auth::user();
    }

    public function updatedCategory()
    {
        $this->videoSubCategories = VideoSubCategory::whereIn('video_category_id', Arr::wrap($this->category))->get();
    }

    protected $rules = [
        'title' => 'required',
        'description' => 'required|string|max:2000',
        'category' => 'required|integer|exists:video_categories,id',
        'subCategory' => 'required|integer|exists:video_sub_categories,id',
        'image' => 'required|image|max:1024',
        'video' => 'required|file|mimes:mp4',
        'creator_code' => 'required|string|exists:users,code',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function save()
    {
        $this->validate();

        $videoName = implode('.', [Str::random(10), $this->video->getClientOriginalExtension()]);
        $imageName = implode('.', [Str::random(10), $this->image->getClientOriginalExtension()]);

        $this->category = VideoCategory::whereId($this->category)->first();

        $this->subCategory = VideoSubCategory::whereId($this->subCategory)->first();

        $videoUrl = url($this->video->storePubliclyAs('uploads/tutorials/' . $this->category->slug . '/' . $this->subCategory->slug, $videoName, 'public'));
        $imageUrl = url($this->image->storePubliclyAs('uploads/tutorials/' . $this->category->slug . '/' . $this->subCategory->slug, $imageName, 'public'));

        $this->subCategory->videos()->create([
            'title' => $this->title,
            'description' => $this->description,
            'creator_code' => $this->creator_code,
            'fileName' => $videoUrl,
            'image' => $imageUrl,
        ]);

        $this->resetExcept(['success', 'videos', 'videoCategories', 'admin']);
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'ویدیو بارگذاری شد']);
        $this->emitSelf('videoCreated');
    }

    public function deleteVideo(Video $video)
    {
        unlink(public_path('uploads/' . $video->fileName));
        unlink(public_path('uploads/' . $video->image));
        $video->delete();
        $this->emitSelf('videoDeleted');
    }

    public function render()
    {
        return view('livewire.videos.listing', [
            'videoCategories' => $this->videoCategories ?? VideoCategory::all(),
            'videos' => $this->videos ?? Video::with(['subCategory', 'interactions', 'views'])->get()
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
