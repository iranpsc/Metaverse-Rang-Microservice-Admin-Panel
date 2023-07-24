<?php

namespace App\Http\Livewire\Videos;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\SendsVerificationSms;

class EditSubCategory extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public $subCategory, $name, $description, $image;

    public function mount()
    {
        $this->name = $this->subCategory->name;
        $this->description = $this->subCategory->description;
        $this->admin = auth()->guard('admin')->user();
    }

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'required|string|max:20000',
        'image' => 'nullable|image|max:5024',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function save()
    {
        $data = $this->validate();

        if ($this->image) {
            $url = $this->image->store('tutorials/' . $this->subCategory->slug, 'public');
            $data['image'] = $url;
        } else {
            $data['image'] = $this->subCategory->image;
        }

        // Pop the phoneVerification and access_password properties from the end of $data
        array_pop($data);
        array_pop($data);

        $this->subCategory->update($data);
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'دسته بندی ویرایش شد']);
        $this->emitUp('categoryUpdated');
    }

    public function render()
    {
        return view('livewire.videos.edit-sub-category');
    }
}
