<?php

namespace App\Http\Livewire\Videos;

use Livewire\Component;
use App\Traits\SendsVerificationSms;
use Livewire\WithFileUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;

class EditVideo extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public $videoDb, $title, $slug, $description, $image, $video;

    protected $rules = [
        'title' => 'required|string|max:255',
        'slug' => 'required|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:videos,slug',
        'description' => 'required|string|max:20000',
        'image' => 'nullable|image|max:1024',
        'video' => 'nullable|string',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = auth()->guard('admin')->user();
        $this->title = $this->videoDb->title;
        $this->slug = $this->videoDb->slug;
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

            if (!file_exists(storage_path('app/public/resumable-tmp/' . $this->video))) {
                $this->addError('video', 'فایل ویدیو را بارگذاری کنید.');
                return;
            } else {
                rename(storage_path('app/public/resumable-tmp/' . $this->video), storage_path('app/public/' . $videoUrl));
            }
        }

        $this->videoDb->update([
            'title' => $this->title,
            'slug' => Str::slug($this->slug),
            'description' => $this->description,
            'fileName' => $videoUrl ?? $this->videoDb->fileName,
            'image' => $imageUrl ?? $this->videoDb->image,
        ]);

        $this->dispatchBrowserEvent('resourceModified', ['message' => 'ویدیو بروزرسانی شد']);
        $this->emitUp('videoUdated');
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

    public function render()
    {
        return view('livewire.videos.edit-video');
    }
}
