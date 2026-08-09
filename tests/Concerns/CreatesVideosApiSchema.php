<?php

namespace Tests\Concerns;

use App\Models\User;
use App\Models\Video;
use App\Models\VideoCategory;
use App\Models\VideoSubCategory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait CreatesVideosApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpVideosApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createVideoCategoriesTable();
        $this->createVideoSubCategoriesTable();
        $this->createVideosTable();
        $this->createInteractionsTable();
        $this->createViewsTable();

        Storage::fake('public');
        Storage::fake('local');
    }

    private function createVideoCategoriesTable(): void
    {
        if (Schema::hasTable('video_categories')) {
            return;
        }

        Schema::create('video_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description');
            $table->string('image');
            $table->string('icon')->nullable();
            $table->timestamps();
        });
    }

    private function createVideoSubCategoriesTable(): void
    {
        if (Schema::hasTable('video_sub_categories')) {
            return;
        }

        Schema::create('video_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('video_category_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description');
            $table->string('image');
            $table->string('icon')->nullable();
            $table->timestamps();
            $table->index('video_category_id');
        });
    }

    private function createVideosTable(): void
    {
        if (Schema::hasTable('videos')) {
            return;
        }

        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('video_sub_category_id');
            $table->string('title');
            $table->string('slug');
            $table->mediumText('description');
            $table->string('fileName');
            $table->string('creator_code');
            $table->string('image');
            $table->timestamps();
            $table->index('video_sub_category_id');
        });
    }

    private function createInteractionsTable(): void
    {
        if (Schema::hasTable('interactions')) {
            return;
        }

        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->string('likeable_type');
            $table->unsignedBigInteger('likeable_id');
            $table->boolean('liked')->default(false);
            $table->timestamps();
            $table->index(['likeable_type', 'likeable_id']);
        });
    }

    private function createViewsTable(): void
    {
        if (Schema::hasTable('views')) {
            return;
        }

        Schema::create('views', function (Blueprint $table) {
            $table->id();
            $table->string('viewable_type');
            $table->unsignedBigInteger('viewable_id');
            $table->timestamps();
            $table->index(['viewable_type', 'viewable_id']);
        });
    }

    protected function createVideoCategory(array $overrides = []): VideoCategory
    {
        return VideoCategory::factory()->create($overrides);
    }

    protected function createVideoSubCategory(?VideoCategory $category = null, array $overrides = []): VideoSubCategory
    {
        $category ??= $this->createVideoCategory();

        return VideoSubCategory::factory()->create(array_merge([
            'video_category_id' => $category->id,
        ], $overrides));
    }

    protected function createVideo(?VideoSubCategory $subCategory = null, array $overrides = []): Video
    {
        $subCategory ??= $this->createVideoSubCategory();

        return Video::factory()->create(array_merge([
            'video_sub_category_id' => $subCategory->id,
        ], $overrides));
    }

    protected function createCreatorUser(array $overrides = []): User
    {
        $code = $overrides['code'] ?? 'USR'.Str::upper(Str::random(6));

        return User::factory()->create(array_merge([
            'code' => $code,
        ], $overrides));
    }

    protected function fakeSvg(string $name = 'icon.svg', int $kilobytes = 10): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kilobytes, 'image/svg+xml');
    }

    protected function putResumableVideoFile(string $fileName = 'clip.mp4', string $contents = 'fake-video-bytes'): string
    {
        Storage::disk('local')->put("resumable-tmp/{$fileName}", $contents);

        return $fileName;
    }

    /**
     * @return array<string, mixed>
     */
    protected function validVideoCategoryStorePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Tutorial Category',
            'slug' => 'tutorial-category-'.Str::lower(Str::random(6)),
            'description' => 'Category description for tutorials.',
            'image' => UploadedFile::fake()->image('category.jpg'),
            'icon' => $this->fakeSvg(),
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validVideoCategoryUpdatePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Updated Category',
            'description' => 'Updated category description.',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validVideoSubCategoryStorePayload(?VideoCategory $category = null, array $overrides = []): array
    {
        $category ??= $this->createVideoCategory();

        return array_merge([
            'video_category_id' => $category->id,
            'name' => 'Tutorial Sub Category',
            'slug' => 'tutorial-sub-'.Str::lower(Str::random(6)),
            'description' => 'Sub category description.',
            'image' => UploadedFile::fake()->image('sub-category.jpg'),
            'icon' => $this->fakeSvg('sub-icon.svg'),
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validVideoSubCategoryUpdatePayload(?VideoCategory $category = null, array $overrides = []): array
    {
        $category ??= $this->createVideoCategory();

        return array_merge([
            'video_category_id' => $category->id,
            'name' => 'Updated Sub Category',
            'description' => 'Updated sub category description.',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validVideoStorePayload(
        ?VideoCategory $category = null,
        ?VideoSubCategory $subCategory = null,
        ?User $creator = null,
        array $overrides = []
    ): array {
        $category ??= $this->createVideoCategory();
        $subCategory ??= $this->createVideoSubCategory($category);
        $creator ??= $this->createCreatorUser();
        $videoFileName = $overrides['video_file_name'] ?? $this->putResumableVideoFile('store-'.Str::lower(Str::random(8)).'.mp4');

        return array_merge([
            'title' => 'Intro Video',
            'description' => 'A detailed video description.',
            'video_category_id' => $category->id,
            'video_sub_category_id' => $subCategory->id,
            'image' => UploadedFile::fake()->image('thumb.jpg'),
            'video_file_name' => $videoFileName,
            'creator_code' => $creator->code,
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validVideoUpdatePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Updated Video Title',
            'description' => 'Updated video description.',
        ], $overrides);
    }
}
