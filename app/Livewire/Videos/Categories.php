<?php

namespace App\Livewire\Videos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\VideoCategory;
use App\Models\VideoSubCategory;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;

class Categories extends Component
{
    use WithPagination, WithFileUploads, SendsVerificationSms;

    public $name, $parentCategory, $image, $slug, $description, $search, $icon;

    protected function rules()
    {
        return [
            'name' => 'required|string',
            'slug' => 'required|string',
            'image' => 'required|image',
            'parentCategory' => 'nullable|integer|exists:video_categories,id',
            'description' => 'required|string|max:20000',
            'icon' => 'required|file|mimes:svg|max:1024',
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

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function save()
    {
        $this->validate();

        $this->slug = trim($this->slug);

        if (empty($this->parentCategory)) {
            $imageUrl = $this->image->store('tutorials/' . $this->slug, 'public');
            $iconUrl = $this->icon->store('tutorials/' . $this->slug, 'public');
            VideoCategory::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'image' => $imageUrl,
                'icon' => $iconUrl,
            ]);
        } else {
            $parentCategory = VideoCategory::findOrFail($this->parentCategory);
            $imageUrl = $this->image->store('tutorials/' . $parentCategory->slug . '/' . $this->slug, 'public');
            $iconUrl = $this->icon->store('tutorials/' . $parentCategory->slug . '/' . $this->slug, 'public');
            $parentCategory->subCategories()->create([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'image' => $imageUrl,
                'icon' => $iconUrl,
            ]);
        }
        $this->reset('name', 'slug', 'image', 'parentCategory', 'description');
        $this->dispatch('notify', message: 'دسته بندی ایجاد شد');
    }

    public function deleteCategory(VideoCategory $category)
    {
        $category->subCategories()->delete();
        $category->delete();
        $this->dispatch('notify', message: 'دسته بندی حذف شد');
    }

    public function deleteSubCategory(VideoSubCategory $videoSubCategory)
    {
        $videoSubCategory->delete();
        $this->dispatch('notify', message: 'زیر دسته بندی حذف شد');
    }

    #[Title('دسته بندی ویدیوها')]
    public function render()
    {
        return view('livewire.videos.categories', [
            'categories' => VideoCategory::with('subCategories')->paginate(5),
        ]);
    }
}
