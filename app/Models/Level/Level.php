<?php

namespace App\Models\Level;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Level\Prize;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'score',
    ];


    public function prize() {
        return $this->hasOne(Prize::class);
    }
}
