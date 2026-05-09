<?php

namespace App\Models\Level;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelLicense extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'create_union' => 'boolean',
        'add_memeber_to_union' => 'boolean',
        'observation_license' => 'boolean',
        'gate_license' => 'boolean',
        'lawyer_license' => 'boolean',
        'city_counsile_entry' => 'boolean',
        'establish_special_residential_property' => 'boolean',
        'establish_property_on_surface' => 'boolean',
        'judge_entry' => 'boolean',
        'upload_image' => 'boolean',
        'delete_image' => 'boolean',
        'inter_level_general_points' => 'boolean',
        'inter_level_special_points' => 'boolean',
        'rent_out_satisfaction' => 'boolean',
        'access_to_answer_questions_unit' => 'boolean',
        'create_challenge_questions' => 'boolean',
        'upload_music' => 'boolean',
    ];
}
