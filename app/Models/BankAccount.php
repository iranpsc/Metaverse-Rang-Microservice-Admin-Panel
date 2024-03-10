<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'errors' => 'array',
    ];

    public function bankable()
    {
        return $this->morphTo();
    }
}
