<?php

namespace App\Http\Livewire\Videos;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use App\Helpers\SMS;

class EditSubCategory extends Component
{
    use WithFileUploads;

    public $subCategory, $name, $description, $image, $phoneVerification, $access_password, $admin;

    public function mount()
    {
        $this->name = $this->subCategory->name;
        $this->description = $this->subCategory->description;
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
                $url = $this->image->storePubliclyAs('tutorials/' . $this->subCategory->slug, $imageName, 'public');
                $data['image'] = $url;
            } else {
                $data['image'] = $this->subCategory->image;
            }

            // Pop the phoneVerification and access_password properties from the end of $data
            array_pop($data);
            array_pop($data);

            $this->subCategory->update($data);
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
        return view('livewire.videos.edit-sub-category');
    }
}
