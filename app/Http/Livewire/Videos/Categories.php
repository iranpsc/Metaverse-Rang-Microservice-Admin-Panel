<?php

namespace App\Http\Livewire\Videos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\VideoCategory;
use App\Models\VideoSubCategory;
use Livewire\WithFileUploads;

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
        'description' => 'required|string|max:20000',
    ];

    public function save()
    {
        $this->validate();

        $this->slug = trim($this->slug);

        if (empty($this->parentCategory)) {
            $url = $this->image->store('tutorials/' . $this->slug, 'public');
            VideoCategory::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'image' => $url,
            ]);
        } else {
            $parentCategory = VideoCategory::findOrFail($this->parentCategory);
            $url = $this->image->store('tutorials/' . $parentCategory->slug . '/' . $this->slug, 'public');
            $parentCategory->subCategories()->create([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'image' => $url,
            ]);
        }
        $this->reset('name', 'slug', 'image', 'parentCategory', 'description');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'دسته بندی ایجاد شد']);
        $this->emitSelf('categoryCreated');
    }

    public function deleteCategory(VideoCategory $category)
    {
        foreach ($category->subCategories as $item) {
            unlink(public_path('uploads/' . $item->image));
            $item->delete();
        }
        unlink(public_path('uploads/' . $category->image));
        $category->delete();
        $this->emitSelf('categoryDeleted');
    }

    public function deleteSubCategory(VideoSubCategory $videoSubCategory)
    {
        unlink(public_path('uploads/' . $videoSubCategory->image));
        $videoSubCategory->delete();
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
