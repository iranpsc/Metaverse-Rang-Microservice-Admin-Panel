<?php

namespace App\Models\Level;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelGem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'fbx_file' => 'array',
        'encryption' => 'boolean',
        'has_animation' => 'boolean',
        'points' => 'integer',
        'lines' => 'integer',
    ];
}
