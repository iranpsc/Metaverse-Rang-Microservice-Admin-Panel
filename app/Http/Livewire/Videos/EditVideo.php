<?php

namespace App\Http\Livewire\Videos;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Helpers\SMS;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class EditVideo extends Component
{
    use WithFileUploads;

    public $videoDb,$title, $description, $image, $video, $code, $accessPassword, $admin;

    protected $rules = [
        'title' => 'required',
        'description' => 'required|string|max:2000',
        'image' => 'nullable|image|max:1024',
        'video' => 'nullable|file|mimes:mp4',
        'code' => 'required|integer',
        'accessPassword' => 'required',
    ];

    public function mount()
    {
        $this->admin = Auth::user();
        $this->title = $this->videoDb->title;
        $this->description = $this->videoDb->description;
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


    public function save()
    {
        $this->validate();

        $cachedCode = Cache::get('videos-verify-code-' . $this->admin->id);

        if (!$cachedCode || !Hash::check($this->code, $cachedCode)) {
            $this->addError('code', 'کد تایید وارد شده صحیح نیست');
        } else if (!Hash::check($this->accessPassword, $this->admin->access_password)) {
            $this->addError('accessPassword', 'رمز دسترسی صحیح نیست');
        } else {

            if($this->image)
            {
                $imageName = implode('.', [Str::random(10), $this->image->getClientOriginalExtension()]);
                $imageUrl = $this->image->storePubliclyAs('tutorials/' . $this->videoDb->categoriable->slug, $imageName, 'public');

            }

            if($this->video) {
                $videoName = implode('.', [Str::random(10), $this->video->getClientOriginalExtension()]);
                $videoUrl = $this->video->storePubliclyAs('tutorials/' . $this->videoDb->categoriable->slug, $videoName, 'public');
            }

            $this->videoDb->update([
                'title' => $this->title,
                'description' => $this->description,
                'fileName' => $videoUrl ?? $this->videoDb->fileName,
                'image' => $imageUrl ?? $this->videoDb->image,
            ]);

            session()->flash('success', 'ویدیو بروزرسانی شد.');
            Cache::forget('videos-verify-code-' . $this->admin->id);
            $this->emitUp('videoUdated');
        }
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.videos.edit-video');
    }
}
