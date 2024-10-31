<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureProperties extends Model
{
    public $incrementing = false;

    protected $casts = [
        'feature_id' => 'int',
        'id' => 'string',
        'density' => 'int',
        'price' => 'float',
        'id_postfix' => 'int',
        'id_prefix' => 'string',
    ];

    protected $guarded = [];

    public function feature()
    {
        return $this->belongsTo(Feature::class, 'feature_id', 'id');
    }

    public function getApplicationTitle()
    {
        return match ($this->karbari) {
            't' => 'تجاری',
            'a' => 'آموزشی',
            's' => 'فضای سبز',
            'm' => 'مسکونی',
            'b' => 'بهداشتی',
            'e' => 'اداری',
            'f' => 'فرهنگی',
            'g' => 'گردشگری',
            'z' => 'مذهبی',
            'n' => 'نمایشگاه',
        };
    }
}
