<?php

namespace App\Services\Videos;

use App\Models\VideoCategory;
use App\Models\VideoSubCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VideoSubCategoryService
{
    /**
     * Retrieve paginated video sub categories with optional filters.
     */
    public function getSubCategories(int $perPage = 10, ?int $categoryId = null, ?string $search = null): LengthAwarePaginator
    {
        $query = VideoSubCategory::query()
            ->with(['category:id,name,slug'])
            ->orderByDesc('created_at');

        if (!empty($categoryId)) {
            $query->where('video_category_id', $categoryId);
        }

        if (!empty($search)) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Store a new sub category under the given category.
     */
    public function create(array $data): VideoSubCategory
    {
        $category = VideoCategory::findOrFail($data['video_category_id']);
        $slug = trim($data['slug']);
        $directory = $this->buildDirectory($category->slug, $slug);

        $imagePath = $this->storeImage($data['image'], $directory);
        $iconPath = $this->storeIcon($data['icon'], $directory);

        /** @var VideoSubCategory $subCategory */
        $subCategory = VideoSubCategory::create([
            'video_category_id' => $category->id,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'image' => $imagePath,
            'icon' => $iconPath,
        ]);

        return $subCategory->fresh(['category']);
    }

    /**
     * Update an existing sub category.
     */
    public function update(VideoSubCategory $videoSubCategory, array $data): VideoSubCategory
    {
        $categoryId = $data['video_category_id'] ?? $videoSubCategory->video_category_id;
        $category = VideoCategory::findOrFail($categoryId);

        $slug = isset($data['slug']) ? trim($data['slug']) : $videoSubCategory->slug;
        $directory = $this->buildDirectory($category->slug, $slug);

        $payload = [
            'video_category_id' => $category->id,
            'name' => $data['name'],
            'description' => $data['description'],
        ];

        if (isset($data['slug'])) {
            $payload['slug'] = $slug;
        }

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $payload['image'] = $this->replaceImage($videoSubCategory, $data['image'], $directory);
        }

        if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
            $payload['icon'] = $this->replaceIcon($videoSubCategory, $data['icon'], $directory);
        }

        $videoSubCategory->update($payload);

        return $videoSubCategory->fresh(['category']);
    }

    /**
     * Delete a sub category along with its stored assets.
     */
    public function delete(VideoSubCategory $videoSubCategory): void
    {
        if (!empty($videoSubCategory->image)) {
            Storage::disk('public')->delete($videoSubCategory->image);
        }

        if (!empty($videoSubCategory->icon)) {
            Storage::disk('public')->delete($videoSubCategory->icon);
        }

        $videoSubCategory->delete();
    }

    private function buildDirectory(string $categorySlug, string $slug): string
    {
        return "tutorials/{$categorySlug}/{$slug}";
    }

    private function storeImage(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    private function storeIcon(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    private function replaceImage(VideoSubCategory $videoSubCategory, UploadedFile $file, string $directory): string
    {
        if (!empty($videoSubCategory->image)) {
            Storage::disk('public')->delete($videoSubCategory->image);
        }

        return $this->storeImage($file, $directory);
    }

    private function replaceIcon(VideoSubCategory $videoSubCategory, UploadedFile $file, string $directory): string
    {
        if (!empty($videoSubCategory->icon)) {
            Storage::disk('public')->delete($videoSubCategory->icon);
        }

        return $this->storeIcon($file, $directory);
    }
}


