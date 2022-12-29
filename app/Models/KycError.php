<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycError extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value'
    ];

    public function errorable()
    {
        return $this->morphTo();
    }
}
