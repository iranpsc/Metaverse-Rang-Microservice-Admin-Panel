<?php

namespace App\Livewire\Videos;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\SendsVerificationSms;
use Illuminate\Validation\Rule;

class EditCategory extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public $category, $name, $description, $image, $icon;

    public function mount()
    {
        $this->name = $this->category->name;
        $this->description = $this->category->description;
        $this->admin = auth()->guard('admin')->user();
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:20000',
            'image' => 'nullable|image|max:5024',
            'icon' => 'nullable|file|mimes:svg|max:1024',
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
        $data = $this->validate();

        if ($this->image) {
            $imageUrl = $this->image->store('tutorials/' . $this->category->slug, 'public');
            $data['image'] = $imageUrl;
        } else {
            $data['image'] = $this->category->image;
        }

        if ($this->icon) {
            $iconUrl = $this->icon->store('tutorials/' . $this->category->slug, 'public');
            $data['icon'] = $iconUrl;
        } else {
            $data['icon'] = $this->category->icon;
        }

        // Pop the phoneVerification and access_password properties from the end of $data
        array_pop($data);
        array_pop($data);

        $this->category->update($data);
        $this->dispatch('notify', message: 'دسته بندی ویرایش شد');
    }

    public function render()
    {
        return view('livewire.videos.edit-category');
    }
}
