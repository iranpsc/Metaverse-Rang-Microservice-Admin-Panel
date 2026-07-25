<?php

namespace App\Services\Videos;

use App\Models\VideoCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VideoCategoryService
{
    /**
     * Retrieve paginated list of video categories with optional search filtering.
     */
    public function getCategories(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $query = VideoCategory::query()
            ->with(['subCategories' => function ($relation) {
                $relation->orderByDesc('created_at');
            }])
            ->withCount('subCategories')
            ->orderByDesc('created_at');

        if (!empty($search)) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new video category.
     */
    public function create(array $data): VideoCategory
    {
        $slug = trim($data['slug']);

        $imagePath = $this->storeImage($data['image'], $slug);
        $iconPath = $this->storeIcon($data['icon'], $slug);

        /** @var VideoCategory $category */
        $category = VideoCategory::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'image' => $imagePath,
            'icon' => $iconPath,
        ]);

        return $category->fresh(['subCategories']);
    }

    /**
     * Update an existing video category.
     */
    public function update(VideoCategory $category, array $data): VideoCategory
    {
        $payload = [
            'name' => $data['name'],
            'description' => $data['description'],
        ];

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $payload['image'] = $this->replaceImage($category, $data['image']);
        }

        if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
            $payload['icon'] = $this->replaceIcon($category, $data['icon']);
        }

        $category->update($payload);

        return $category->fresh(['subCategories']);
    }

    /**
     * Delete a category and its related assets.
     */
    public function delete(VideoCategory $category): void
    {
        // Delete files if they exist.
        if (!empty($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        if (!empty($category->icon)) {
            Storage::disk('public')->delete($category->icon);
        }

        // Cascade delete sub categories first to respect foreign keys.
        $category->subCategories()->delete();

        $category->delete();
    }

    private function storeImage(UploadedFile $file, string $slug): string
    {
        return $file->store("tutorials/{$slug}", 'public');
    }

    private function storeIcon(UploadedFile $file, string $slug): string
    {
        return $file->store("tutorials/{$slug}", 'public');
    }

    private function replaceImage(VideoCategory $category, UploadedFile $file): string
    {
        if (!empty($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $slug = trim($category->slug);

        return $this->storeImage($file, $slug);
    }

    private function replaceIcon(VideoCategory $category, UploadedFile $file): string
    {
        if (!empty($category->icon)) {
            Storage::disk('public')->delete($category->icon);
        }

        $slug = trim($category->slug);

        return $this->storeIcon($file, $slug);
    }
}


