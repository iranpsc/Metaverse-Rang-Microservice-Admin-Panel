<?php

namespace App\Http\Livewire\Videos;

use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;

class EditCategory extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public $category, $name, $description, $image;

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
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function save()
    {
        $data = $this->validate();

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
        session()->flash('success', 'دسته بندی ویرایش شد.');
        $this->emitUp('categoryUpdated');
    }

    public function render()
    {
        return view('livewire.videos.edit-category');
    }
}
