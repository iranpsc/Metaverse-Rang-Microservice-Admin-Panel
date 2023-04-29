<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function categoriable()
    {
        return $this->morphTo();
    }

    public function interactions()
    {
        return $this->morphMany(Interaction::class, 'likeable');
    }

    public function views()
    {
        return $this->morphMany(View::class, 'viewable');
    }
}
