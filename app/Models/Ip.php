<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ip extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function from():Attribute
    {
        return Attribute::make(
            get: fn($value) => long2ip($value),
            set: fn($value) => ip2long($value)
        );
    }

    protected function to():Attribute
    {
        return Attribute::make(
            get: fn($value) => long2ip($value),
            set: fn($value) => ip2long($value)
        );
    }

    public function scopeAdmin($query)
    {
        return $query->where('type', 'admin');
    }

}
