<?php

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $attirbutes = [
        'status' => 0
    ];

    protected $connection = 'sqlite';

    public $timestamps = false;

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function modals()
    {
        return $this->hasMany(Modal::class);
    }
}
