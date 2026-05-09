<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function subCategories()
    {
        return $this->hasMany(VideoSubCategory::class);
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
