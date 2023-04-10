<?php

namespace App\Http\Livewire\Videos;

use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use App\Helpers\SMS;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class EditCategory extends Component
{
    use WithFileUploads;

    public $category, $name, $description, $image, $phoneVerification, $access_password, $admin;

    public function mount()
    {
        $this->name = $this->category->name;
        $this->description = $this->category->description;
        $this->admin = Auth::guard('admin')->user();
    }

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'required|string|max:2000',
        'image' => 'nullable|image|max:5024',
        'phoneVerification' => 'required|integer',
        'access_password' => 'required'
    ];

    public function sendSMS()
    {
        $verify_code = random_int(100000, 999999);
        Cache::put('verify_code', Hash::make($verify_code), now()->addMinutes(2));
        $result = SMS::send($this->admin->phone, $verify_code);
        if (is_array($result)) {
            foreach ($result as $r) {
                session()->flash('success', $r->statustext);
            }
        } else {
            session()->flash('error', explode(":", $result)[1]);
        }
    }

    public function save()
    {
        $data = $this->validate();

        if (!Hash::check($this->phoneVerification, Cache::get('verify_code'))) {
            $this->addError('phoneVerification', 'کد تایید وارد شده صحیح نمی باشد');
        } else if (!password_verify($this->access_password, $this->admin->access_password)) {
            $this->addError('access_password', 'رمز دسترسی صحیح نمی باشد');
        } else {
            if ($this->image) {
                $imageName = implode('.', [Str::random(10), $this->image->getClientOriginalExtension()]);
                $url = $this->image->storePubliclyAs('tutorials/' . $this->category->slug, $imageName, 'public');
                $data['image'] = $url;
            } else {
                $data['image'] = $this->category->image;
            }

            // Pop the phoneVerification and access_password properties from the end of $data
            array_pop($data);
            array_pop($data);

            $this->category->update($data);
            Cache::forget('verify_code');
            session()->flash('success', 'دسته بندی ویرایش شد.');
            $this->emitUp('categoryUpdated');
        }
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.videos.edit-category');
    }
}
