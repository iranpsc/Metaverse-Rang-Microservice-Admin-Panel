<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected $attributes = [
        'color' => '#000000',
        'is_version' => false,
    ];

    public function scopeVersion($query)
    {
        return $query->where('is_version', true);
    }

    public function scopeEvent($query)
    {
        return $query->where('is_version', false);
    }

    public function interactions() {
        return $this->morphMany(Interaction::class, 'likeable');
    }

    public function views() {
        return $this->morphMany(View::class, 'viewable');
    }

    public function getStatus()
    {
        if($this->is_version) return '---';
        return $this->ends_at < now() ? 'سپری شده' : 'در حال برگزاری';
    }
}

