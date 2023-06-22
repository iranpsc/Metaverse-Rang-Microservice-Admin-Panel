<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureProperties extends Model
{
    public $incrementing = false;

    protected $casts = [
        'feature_id' => 'int'
    ];

    protected $fillable = [
        'id',
        'feature_id',
        'name',
        'owner',
        'address',
        'density',
        'date',
        'stability',
        'label',
        'price',
        'region',
        'area',
        'karbari',
        'status',
        'rgb'
    ];

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
