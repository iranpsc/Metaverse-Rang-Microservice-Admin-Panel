<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'status' => 1,
    ];

    public function bankable()
    {
        return $this->morphTo();
    }

    public function errors()
    {
        return $this->morphMany(KycError::class, 'errorable');
    }

}
