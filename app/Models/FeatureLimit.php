<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureLimit extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts()
    {
        return [
            'verified_kyc_limit' => 'boolean',
            'verified_bank_account_limit' => 'boolean',
            'not_sellable' => 'boolean',
            'under_18_limit' => 'boolean',
            'more_than_18_limit' => 'boolean',
            'dynasty_owner_limit' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'float',
            'individual_buy_count' => 'integer',
            'price_limit' => 'boolean',
            'individual_buy_limit' => 'boolean',
        ];
    }
}
