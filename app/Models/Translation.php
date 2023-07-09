<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'status',
        'path'
    ];

    protected $attirbutes = [
        'status' => 0
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
