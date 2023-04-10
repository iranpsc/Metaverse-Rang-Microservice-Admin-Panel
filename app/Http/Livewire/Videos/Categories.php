<?php

namespace App\Http\Livewire\Videos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\VideoCategory;
use App\Models\VideoSubCategory;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class Categories extends Component
{
    use WithPagination, WithFileUploads;

    public $name, $parentCategory, $image, $slug, $description;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'categoryCreated' => '$refresh',
        'deleteVideoCategory' => 'deleteCategory',
        'deleteVideoSubCategory' => 'deleteSubCategory',
        'categoryDeleted' => '$refresh',
        'categoryUpdated' => '$refresh'
    ];

    protected $rules = [
        'name' => 'required|string',
        'slug' => 'required|string',
        'image' => 'required|image',
        'parentCategory' => 'nullable|integer|exists:video_categories,id',
        'description' => 'required|string|max:2000',
    ];

    protected $messages = [
        'name.required' => 'نام دسته بندی را وارد کنید.',
        'slug.required' => 'اسلاگ را وارد کنید.',
    ];

    public function save()
    {
        $this->validate();

        $imageName = implode('.', [Str::random(10), $this->image->getClientOriginalExtension()]);

        $this->slug = trim($this->slug);

        if(empty($this->parentCategory)) {
            $url = $this->image->storePubliclyAs('tutorials/'.$this->slug, $imageName, 'public');
            VideoCategory::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'image' => $url,
            ]);

        } else {

            $parentCategory = VideoCategory::findOrFail($this->parentCategory);
            $url = $this->image->storePubliclyAs('tutorials/'.$parentCategory->slug.'/'.$this->slug, $imageName, 'public');
            $parentCategory->subCategories()->create([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'image' => $url,
            ]);
        }
        session()->flash('success', 'دسته بندی ایجاد شد.');
        $this->reset('name', 'slug', 'image', 'parentCategory', 'description');
        $this->emitSelf('categoryCreated');
    }

    public function deleteCategory(VideoCategory $category)
    {
        foreach($category->subCategories as $item) {
            unlink(public_path('uploads/'.$item->image));
            rmdir(public_path('uploads/tutorials/'. $category->slug . '/' .$item->slug));
            $item->delete();
        }
        unlink(public_path('uploads/'.$category->image));
        rmdir(public_path('uploads/tutorials/'.$category->slug));
        session()->flash('success', 'دسته بندی حذف شد.');
        $category->delete();
        $this->emitSelf('categoryDeleted');
    }

    public function deleteSubCategory(VideoSubCategory $videoSubCategory)
    {
        unlink(public_path('uploads/'.$videoSubCategory->image));
        rmdir(public_path('uploads/tutorials/'. $videoSubCategory->category->slug . '/' .$videoSubCategory->slug));
        $videoSubCategory->delete();
        session()->flash('success', 'دسته بندی حذف شد.');
        $this->emitSelf('categoryDeleted');
    }
    public function render()
    {
        return view('livewire.videos.categories', [
            'categories' => VideoCategory::with('subCategories')->paginate(10),
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}
