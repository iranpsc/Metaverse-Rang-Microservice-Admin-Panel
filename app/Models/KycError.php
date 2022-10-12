<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycError extends Model
{
    use HasFactory;

    protected $fillable = [
        'kyc_id',
        'fname_err',
        'lname_err',
        'father_name_err',
        'melli_code_err',
        'province_err',
        'city_err',
        'street_err',
        'number_err',
        'postal_code_err',
        'address_err',
        'melli_card_err',
        'prove_picture_err',
        'resume_err',
    ];
}
