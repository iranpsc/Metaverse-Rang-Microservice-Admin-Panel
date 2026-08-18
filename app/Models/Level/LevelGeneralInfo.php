<?php

namespace App\Models\Level;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelGeneralInfo extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'fbx_file' => 'array',
        'has_animation' => 'boolean',
        'score' => 'integer',
        'rank' => 'integer',
        'subcategories' => 'integer',
        'points' => 'integer',
        'lines' => 'integer',
    ];
}
