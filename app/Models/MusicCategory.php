<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MusicCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function musics()
    {
        return $this->hasMany(Music::class);
    }
}
