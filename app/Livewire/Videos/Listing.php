<?php

namespace App\Livewire\Videos;

use App\Models\Video;
use App\Models\VideoCategory;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\VideoSubCategory;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;

class Listing extends Component
{
    use WithPagination, WithFileUploads, SendsVerificationSms;

    public $title, $description, $category, $sub_category, $image, $video, $creator_code, $search;

    public $videoSubCategories = [];

    public function mount()
    {
        $this->admin = auth()->guard('admin')->user();
    }

    public function updatedCategory()
    {
        $this->videoSubCategories = VideoSubCategory::whereIn('video_category_id', [$this->category])->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:20000',
            'category' => 'required|integer|exists:video_categories,id',
            'sub_category' => 'required|integer|exists:video_sub_categories,id',
            'image' => 'required|image|max:1024',
            'video' => 'required|string',
            'creator_code' => 'required|string|exists:users,code',
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

    public function save()
    {
        $this->validate();

        $this->category = VideoCategory::whereId($this->category)->first();

        $this->sub_category = VideoSubCategory::whereId($this->sub_category)->first();

        $videoUrl = 'tutorials/' . $this->category->slug . '/' . $this->sub_category->slug . '/' . $this->video;

        if (file_exists(storage_path('app/resumable-tmp/' . $this->video))) {
            if (!file_exists(storage_path('app/public/' . 'tutorials/' . $this->category->slug . '/' . $this->sub_category->slug))) {
                mkdir(storage_path('app/public/' . 'tutorials/' . $this->category->slug . '/' . $this->sub_category->slug), 0755, true);
            }
            rename(storage_path('app/resumable-tmp/' . $this->video), storage_path('app/public/' . $videoUrl));
        } else {
            $this->addError('video', 'ویدیو بارگذاری نشده است');
            return;
        }

        $imageUrl = $this->image->store('tutorials/' . $this->category->slug . '/' . $this->sub_category->slug, 'public');

        $this->sub_category->videos()->create([
            'title' => $this->title,
            'slug' => Str::random(15),
            'description' => $this->description,
            'creator_code' => strtolower($this->creator_code),
            'fileName' => $videoUrl,
            'image' => $imageUrl,
        ]);

        $this->resetExcept(['videoCategories', 'admin']);
        $this->dispatch('notify', message: 'ویدیو بارگذاری شد');
    }

    public function delete(Video $video)
    {
        unlink(public_path('uploads/' . $video->fileName));
        unlink(public_path('uploads/' . $video->image));
        $video->delete();
    }

    #[Title('ویدیوها')]
    public function render()
    {
        $videosQuery = Video::with(['category', 'interactions', 'views']);

        // Apply search filter if search term is provided
        if (!empty($this->search)) {
            $videosQuery->where('title', 'like', '%' . $this->search . '%');
        }

        return view('livewire.videos.listing', [
            'videoCategories' => VideoCategory::all(),
            'videos' => $videosQuery->latest()->paginate(10)
        ]);
    }
}
