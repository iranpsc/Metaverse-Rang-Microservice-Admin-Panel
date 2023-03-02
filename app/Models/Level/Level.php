<?php

namespace App\Models\Level;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Level\Prize;

class Level extends Model
{
    use HasFactory;

    protected $guraded = [];


    public function prize() {
        return $this->hasOne(Prize::class);
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
