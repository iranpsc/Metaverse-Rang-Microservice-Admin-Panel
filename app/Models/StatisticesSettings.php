<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatisticesSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value'
    ];

    public $timestamps = false;

    public function StatisticesTypesSettings()
    {
        return $this->belongsToMany(StatisticesTypes::class , 'statistices_types_settings');
    }
}
