<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function interactions() {
        return $this->morphMany(Interaction::class, 'likeable');
    }

    public function views() {
        return $this->morphMany(View::class, 'viewable');
    }

    public function getStatus()
    {
        return $this->ends_at < now() ? 'سپری شده' : 'در حال برگزاری';
    }
}

