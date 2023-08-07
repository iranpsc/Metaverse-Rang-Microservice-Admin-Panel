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
use Illuminate\Http\Request;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;

class Listing extends Component
{
    use WithPagination, WithFileUploads, SendsVerificationSms;

    public $title, $description, $category, $subCategory, $image, $video, $creator_code;

    public $videoSubCategories;

    protected $listeners = [
        'videoCreated' => '$refresh',
        'videoUpdated' => '$refresh',
        'videoDeleted' => '$refresh',
        'deleteTrainingVideo' => 'deleteVideo',
    ];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->admin = auth()->guard('admin')->user();
    }

    public function updatedCategory()
    {
        $this->videoSubCategories = VideoSubCategory::whereIn('video_category_id', Arr::wrap($this->category))->get();
    }

    protected $rules = [
        'title' => 'required',
        'description' => 'required|string|max:20000',
        'category' => 'required|integer|exists:video_categories,id',
        'subCategory' => 'required|integer|exists:video_sub_categories,id',
        'image' => 'required|image|max:1024',
        'video' => 'required|string',
        'creator_code' => 'required|string|exists:users,code',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function save()
    {
        $this->validate();

        $this->category = VideoCategory::whereId($this->category)->first();

        $this->subCategory = VideoSubCategory::whereId($this->subCategory)->first();

        $videoUrl = 'tutorials/' . $this->category->slug . '/' . $this->subCategory->slug . '/' . $this->video;

        if (!file_exists(storage_path('app/public/resumable-tmp/' . $this->video))) {
            $this->addError('video', 'فایل ویدیو را بارگذاری کنید.');
            return;
        } else {
            rename(storage_path('app/public/resumable-tmp/' . $this->video), storage_path('app/public/'.$videoUrl));
        }

        $imageUrl = $this->image->store('tutorials/' . $this->category->slug . '/' . $this->subCategory->slug, 'public');

        $this->subCategory->videos()->create([
            'title' => $this->title,
            'description' => $this->description,
            'creator_code' => strtolower($this->creator_code),
            'fileName' => $videoUrl,
            'image' => $imageUrl,
        ]);

        $this->resetExcept(['success', 'videos', 'videoCategories', 'admin']);
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'ویدیو بارگذاری شد']);
        $this->emitSelf('videoCreated');
    }

    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|max:2024']);

        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        $fileReceived = $receiver->receive(); // receive file

        if ($fileReceived->isFinished()) { // file uploading is complete / all chunks are uploaded
            $file = $fileReceived->getFile(); // get file
            $extension = $file->getClientOriginalExtension();
            $fileName = md5(time()) . '.' . $extension; // a unique file name

            $file->storeAs('resumable-tmp', $fileName, 'public');

            unlink($file->getPathname());

            return response()->json(['fileName' => $fileName]);
        }

        // otherwise return percentage information
        $handler = $fileReceived->handler();

        return response()->json([
            'done' => $handler->getPercentageDone(),
            'status' => true
        ]);
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
            'videoCategories' => VideoCategory::all(),
            'videos' => Video::with(['category', 'interactions', 'views'])->simplePaginate(10)
        ]);
    }
}
