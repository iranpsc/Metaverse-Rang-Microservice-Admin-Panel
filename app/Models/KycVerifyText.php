<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KycVerifyText extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function kycs(): HasMany
    {
        return $this->hasMany(Kyc::class, 'verify_text_id');
    }
}
