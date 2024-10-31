<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array
     */
    protected function casts()
    {
        return [
            'errors' => 'array',
        ];
    }

    /**
     * Get the user that owns the Kyc.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the status badge attribute.
     *
     * @return string
     */
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            0 => '<span class="badge badge-info">در انتظار بررسی</span>',
            1 => '<span class="badge badge-success">تایید شده</span>',
            -1 => '<span class="badge badge-danger">رد شده</span>',
            default => '<span class="badge badge-warning">نامشخص</span>',
        };
    }

    /**
     * Get the verify text that owns the Kyc.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifyText()
    {
        return $this->hasOne(KycVerifyText::class, 'id', 'verify_text_id');
    }
}
