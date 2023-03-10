<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
    use HasFactory;

    protected $fillable = [
        'shaba',
        'bank',
        'melli_card',
        'prove_picture',
        'resume',
        'fname',
        'lname',
        'father_name',
        'melli_code',
        'phone',
        'email',
        'province',
        'city',
        'street',
        'number',
        'postal_code',
        'address',
        'site',
        'status',
        'user_id'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function errors()
    {
        return $this->morphMany(KycError::class, 'errorable');
    }

    protected function melliCard():Attribute
    {
        return Attribute::make(
            get: fn($value) => config('rgb.ftp_end_point').$value
        );
    }

    protected function provePicture():Attribute
    {
        return Attribute::make(
            get: fn($value) => config('rgb.ftp_end_point').$value
        );
    }

    protected function resume():Attribute
    {
        return Attribute::make(
            get: fn($value) => config('rgb.ftp_end_point').$value
        );
    }
}
