<?php

namespace App\Http\Livewire\Videos;

use App\Models\Video;
use App\Models\VideoCategory;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;
use App\Helpers\SMS;
use App\Models\VideoSubCategory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Listing extends Component
{
    use WithPagination, WithFileUploads;

    public $title, $description, $category, $subCategory, $image, $video, $code, $accessPassword, $creator_code;

    private $videos, $videoCategories;

    public $videoSubCategories;

    public $admin;

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

    public function sendSMS()
    {
        $verifyCode = random_int(10000, 99999);
        Cache::put('videos-verify-code-' . $this->admin->id, Hash::make($verifyCode), now()->addMinutes(2));
        $result = SMS::send($this->admin->phone, $verifyCode);

        if (is_array($result)) {
            foreach ($result as $r) {
                session()->flash('success', $r->statustext);
            }
        } else {
            session()->flash('error', explode(":", $result)[1]);
            Cache::forget('videos-verify-code-' . $this->admin->id);
        }
    }

    protected $rules = [
        'title' => 'required',
        'description' => 'required|string|max:2000',
        'category' => 'nullable|integer|exists:video_categories,id',
        'subCategory' => 'nullable|integer|exists:video_sub_categories,id',
        'image' => 'required|image|max:1024',
        'video' => 'required|file|mimes:mp4',
        'code' => 'required|integer',
        'accessPassword' => 'required',
        'creator_code' => 'required|exists:users,code'
    ];

    public function save()
    {
        $this->validate();

        $cachedCode = Cache::get('videos-verify-code-' . $this->admin->id);

        if (!$cachedCode || !Hash::check($this->code, $cachedCode)) {
            $this->addError('code', 'کد تایید وارد شده صحیح نیست');
        } else if (!Hash::check($this->accessPassword, $this->admin->access_password)) {
            $this->addError('accessPassword', 'رمز دسترسی صحیح نیست');
        } else {

            $videoName = implode('.', [Str::random(10), $this->video->getClientOriginalExtension()]);
            $imageName = implode('.', [Str::random(10), $this->image->getClientOriginalExtension()]);
            $this->category = VideoCategory::whereId($this->category)->first();

            if (!empty($this->category) && !empty($this->subCategory)) {
                $this->subCategory = VideoSubCategory::whereId($this->subCategory)->first();

                $videoUrl = $this->video->storePubliclyAs('tutorials/' . $this->category->slug . '/' . $this->subCategory->slug, $videoName, 'public');
                $imageUrl = $this->image->storePubliclyAs('tutorials/' . $this->category->slug . '/' . $this->subCategory->slug, $imageName, 'public');

                $this->subCategory->videos()->create([
                    'title' => $this->title,
                    'description' => $this->description,
                    'creator_code' => $this->creator_code,
                    'fileName' => $videoUrl,
                    'image' => $imageUrl,
                ]);

            } else {
                $videoUrl = $this->video->storePubliclyAs('tutorials/' . $this->category->slug, $videoName, 'public');
                $imageUrl = $this->image->storePubliclyAs('tutorials/' . $this->category->slug, $imageName, 'public');
                $this->category->videos()->create([
                    'title' => $this->title,
                    'description' => $this->description,
                    'creator_code' => $this->creator_code,
                    'fileName' => $videoUrl,
                    'image' => $imageUrl,
                ]);
            }

            $this->resetExcept(['success', 'videos', 'videoCategories', 'admin']);
            session()->flash('success', 'ویدیو بارگذاری شد.');
            Cache::forget('videos-verify-code-' . $this->admin->id);
            $this->emitSelf('videoCreated');
        }
    }

    public function deleteVideo(Video $video)
    {
        unlink(public_path('uploads/'.$video->fileName));
        unlink(public_path('uploads/'.$video->image));
        $video->delete();
        $this->emitSelf('videoDeleted');
        session()->flash('success', 'ویدیو حذف شد.');
    }

    public function render()
    {
        return view('livewire.videos.listing', [
            'videoCategories' => $this->videoCategories ?? VideoCategory::all(),
            'videos' => $this->videos ?? Video::with('categoriable')->get()
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
