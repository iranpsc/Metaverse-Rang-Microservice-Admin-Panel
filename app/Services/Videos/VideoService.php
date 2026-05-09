<?php

namespace App\Services\Videos;

use App\Models\Video;
use App\Models\VideoSubCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class VideoService
{
    public function getVideos(int $perPage = 10, ?string $search = null, ?int $categoryId = null, ?int $subCategoryId = null): LengthAwarePaginator
    {
        $query = Video::query()
            ->with(['category.category'])
            ->withCount([
                'views',
                'interactions as likes_count' => fn ($builder) => $builder->where('liked', 1),
                'interactions as dislikes_count' => fn ($builder) => $builder->where('liked', 0),
            ])
            ->orderByDesc('created_at');

        if (! empty($search)) {
            $query->where('title', 'like', "%{$search}%");
        }

        if (! empty($categoryId)) {
            $query->whereHas('category', function ($builder) use ($categoryId) {
                $builder->where('video_category_id', $categoryId);
            });
        }

        if (! empty($subCategoryId)) {
            $query->where('video_sub_category_id', $subCategoryId);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Video
    {
        /** @var VideoSubCategory $subCategory */
        $subCategory = VideoSubCategory::with('category')->findOrFail($data['video_sub_category_id']);

        $directory = $this->buildDirectory($subCategory);

        $videoPath = $this->moveResumableFile($data['video_file_name'], $directory);
        $imagePath = $this->storeImage($data['image'], $directory);

        $video = $subCategory->videos()->create([
            'title' => $data['title'],
            'slug' => Str::random(15),
            'description' => $data['description'],
            'creator_code' => strtolower($data['creator_code']),
            'fileName' => $videoPath,
            'image' => $imagePath,
        ]);

        return $video->fresh(['category.category'])
            ->loadCount([
                'views',
                'interactions as likes_count' => fn ($builder) => $builder->where('liked', 1),
                'interactions as dislikes_count' => fn ($builder) => $builder->where('liked', 0),
            ]);
    }

    public function update(Video $video, array $data): Video
    {
        $video->loadMissing('category.category');

        $payload = [
            'title' => $data['title'],
            'description' => $data['description'],
        ];

        if (! empty($data['creator_code'])) {
            $payload['creator_code'] = strtolower($data['creator_code']);
        }

        $directory = $this->buildDirectory($video->category);

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $payload['image'] = $this->replaceImage($video, $data['image'], $directory);
        }

        if (! empty($data['video_file_name'])) {
            $payload['fileName'] = $this->replaceVideoFile($video, $data['video_file_name'], $directory);
        }

        $video->update($payload);

        return $video->fresh(['category.category'])
            ->loadCount([
                'views',
                'interactions as likes_count' => fn ($builder) => $builder->where('liked', 1),
                'interactions as dislikes_count' => fn ($builder) => $builder->where('liked', 0),
            ]);
    }

    public function delete(Video $video): void
    {
        if (! empty($video->fileName)) {
            Storage::disk('public')->delete($video->fileName);
        }

        if (! empty($video->image)) {
            Storage::disk('public')->delete($video->image);
        }

        $video->delete();
    }

    private function buildDirectory(VideoSubCategory $subCategory): string
    {
        $subCategory->loadMissing('category');

        $categorySlug = $subCategory->category?->slug ?? 'unknown-category';
        $subCategorySlug = $subCategory->slug ?? 'unknown-sub-category';

        return "tutorials/{$categorySlug}/{$subCategorySlug}";
    }

    private function moveResumableFile(string $fileName, string $directory): string
    {
        $temporaryPath = "resumable-tmp/{$fileName}";

        if (! Storage::disk('local')->exists($temporaryPath)) {
            throw new RuntimeException('ویدیو بارگذاری نشده است.');
        }

        Storage::disk('public')->makeDirectory($directory);

        $finalPath = "{$directory}/{$fileName}";

        $stream = Storage::disk('local')->readStream($temporaryPath);
        if ($stream === false) {
            throw new RuntimeException('امکان خواندن فایل موقت ویدیو وجود ندارد.');
        }

        $written = Storage::disk('public')->put($finalPath, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        if (! $written) {
            throw new RuntimeException('امکان ذخیره فایل ویدیو وجود ندارد.');
        }

        Storage::disk('local')->delete($temporaryPath);

        return $finalPath;
    }

    private function storeImage(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    private function replaceImage(Video $video, UploadedFile $file, string $directory): string
    {
        if (! empty($video->image)) {
            Storage::disk('public')->delete($video->image);
        }

        return $this->storeImage($file, $directory);
    }

    private function replaceVideoFile(Video $video, string $fileName, string $directory): string
    {
        $newPath = $this->moveResumableFile($fileName, $directory);

        if (! empty($video->fileName) && $video->fileName !== $newPath) {
            Storage::disk('public')->delete($video->fileName);
        }

        return $newPath;
    }
}
