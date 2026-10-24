<?php

namespace App\Services\ContentWebhooks;

use App\Models\Calendar;
use App\Models\Image;
use App\Models\Level\Level;
use App\Models\Video;
use App\Models\VideoCategory;
use App\Models\VideoSubCategory;
use Illuminate\Database\Eloquent\Model;

class RouteMapper
{
    public function map(Model $model, string $event): ?array
    {
        $id = (string) $model->getKey();
        $resource = 'record';
        $feature = null;
        $category = null;
        $routes = [];
        $name = $model->name ?? $model->title ?? $id;
        if ($model instanceof Image && $model->imageable instanceof Level) {
            $model = $model->imageable;
            $id = (string) $model->id;
            $name = $model->name;
            $resource = 'image';
        }
        if ($model instanceof Level) {
            $feature = 'citizen_levels';
            $category = 'levels';
            $routes = ["/api/levels/{$id}"];
            if ($event !== 'updated' || $resource === 'image' || array_intersect(array_keys($model->getChanges()), ['name', 'slug', 'score', 'background_image'])) {
                $routes[] = '/api/levels';
            }
            if ($event === 'deleted' && $resource === 'record') {
                foreach (['general-info', 'gem', 'gift', 'licenses', 'prize'] as $part) {
                    $routes[] = "/api/levels/{$id}/{$part}";
                }
            }
        } elseif (str_starts_with($model::class, 'App\\Models\\Level\\')) {
            $resource = match (class_basename($model)) {
                'LevelGeneralInfo' => 'general-info', 'LevelGem' => 'gem', 'LevelGift' => 'gift',
                'LevelLicense' => 'licenses', 'LevelPrize' => 'prize', default => null,
            };
            if (! $resource) {
                return null;
            }
            $id = (string) $model->level_id;
            $name = Level::find($id)?->name ?? $id;
            $feature = 'citizen_levels_details';
            $category = 'levels';
            $routes = ["/api/levels/{$id}", "/api/levels/{$id}/{$resource}"];
        } elseif ($model instanceof Calendar) {
            $feature = $category = $model->is_version ? 'versions' : 'calendar';
            $routes = ['/api/calendar', "/api/calendar/{$id}"];
            if ($model->is_version || $model->getOriginal('is_version')) {
                $routes[] = '/api/calendar/latest-version';
            }
        } elseif ($model instanceof Video) {
            $feature = 'videos';
            $category = 'tutorials';
            $routes = ['/api/tutorials', '/api/video-tutorials', '/api/tutorials/search'];
            foreach (array_unique([$model->slug, $model->getOriginal('slug')]) as $slug) {
                if ($slug) {
                    $routes[] = '/api/tutorials/'.rawurlencode($slug);
                }
            }
            foreach (array_unique([$model->video_sub_category_id, $model->getOriginal('video_sub_category_id')]) as $parent) {
                $sub = VideoSubCategory::find($parent);
                if ($sub) {
                    $routes = [...$routes, ...$this->subRoutes($sub)];
                }
            }
            if ($event === 'deleted') {
                $routes[] = "/api/tutorials/{$id}/interactions";
            }
        } elseif ($model instanceof VideoCategory) {
            $feature = 'video_categories';
            $category = 'tutorials';
            $routes = ['/api/tutorials/categories'];
            foreach (array_unique([$model->slug, $model->getOriginal('slug')]) as $slug) {
                if ($slug) {
                    $routes = [...$routes, ...$this->categoryRoutes($slug)];
                    if ($event === 'updated' && $model->slug !== $model->getOriginal('slug')) {
                        foreach ($model->subCategories as $sub) {
                            $routes[] = '/api/tutorials/categories/'.rawurlencode($slug).'/'.rawurlencode($sub->slug);
                        }
                    }
                }
            }
            if ($event === 'deleted') {
                foreach ($model->subCategories as $sub) {
                    $routes = [...$routes, ...$this->subRoutes($sub)];
                    foreach ($sub->videos as $video) {
                        $routes = [...$routes, ...$this->map($video, 'deleted')['affected_routes']];
                    }
                }
            }
        } elseif ($model instanceof VideoSubCategory) {
            $feature = 'video_sub_categories';
            $category = 'tutorials';
            $routes = ['/api/tutorials/categories', ...$this->subRoutes($model)];
            $old = new VideoSubCategory;
            $old->setRawAttributes($model->getOriginal());
            $routes = [...$routes, ...$this->subRoutes($old)];
            if ($event === 'deleted') {
                foreach ($model->videos as $video) {
                    $routes = [...$routes, ...$this->map($video, 'deleted')['affected_routes']];
                }
            }
        }
        if (! $feature) {
            return null;
        }

        return ['feature' => $feature, 'category' => $category, 'entity_id' => $id,
            'resource' => $resource, 'name' => $name, 'affected_routes' => array_values(array_unique($routes))];
    }

    private function categoryRoutes(string $slug): array
    {
        $base = '/api/tutorials/categories/'.rawurlencode($slug);

        return [$base, $base.'/videos'];
    }

    private function subRoutes(VideoSubCategory $sub): array
    {
        $parent = VideoCategory::find($sub->video_category_id);
        if (! $parent || ! $sub->slug) {
            return [];
        }

        return [...$this->categoryRoutes($parent->slug), '/api/tutorials/categories/'.rawurlencode($parent->slug).'/'.rawurlencode($sub->slug)];
    }
}
