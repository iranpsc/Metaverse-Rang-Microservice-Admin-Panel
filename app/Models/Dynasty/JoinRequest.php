<?php

namespace App\Models\Dynasty;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoinRequest extends Model
{
    use HasFactory;

    protected $fillable=['status'];

    protected static function booted()
    {
        static::addGlobalScope('pending', function(Builder $builder) {
            $builder->where('status', 5)->orWhere('status', 6);
        });
    }

    // public function scopePending($query) {
    //     $query->where('status', 4);
    // }
}
