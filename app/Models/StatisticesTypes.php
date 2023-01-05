<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatisticesTypes extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value'
    ];

    public function StatisticesTypesSettings()
    {
        return $this->hasMany(StatisticesSettings::class);
    }
}
