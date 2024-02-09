<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Video extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function setSlugAttribute($value)
    {
        while (Video::where('slug', $value)->exists()) {
            $value = Str::random(15);
        }

        $this->attributes['slug'] = $value;
    }

    public function category()
    {
        return $this->belongsTo(VideoSubCategory::class, 'video_sub_category_id', 'id');
    }

    public function interactions()
    {
        return $this->morphMany(Interaction::class, 'likeable');
    }

    public function views()
    {
        return $this->morphMany(View::class, 'viewable');
    }
}
