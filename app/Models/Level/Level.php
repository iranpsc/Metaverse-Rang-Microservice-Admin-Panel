<?php

namespace App\Models\Level;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Level extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function generalInfo(): HasOne
    {
        return $this->hasOne(LevelGeneralInfo::class);
    }

    public function licenses(): HasOne
    {
        return $this->hasOne(LevelLicense::class);
    }

    public function gem(): HasOne
    {
        return $this->hasOne(LevelGem::class);
    }

    public function prize(): HasOne
    {
        return $this->hasOne(LevelPrize::class);
    }

    public function gift(): HasOne
    {
        return $this->hasOne(LevelGift::class);
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
