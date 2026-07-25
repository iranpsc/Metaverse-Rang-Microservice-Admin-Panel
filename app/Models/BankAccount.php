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
}
