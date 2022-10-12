<?php

namespace App\Models\Level;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use HasFactory;

    protected $table = 'user_logs';

    public function user() {
        return $this->belongsTo(User::class);
    }
}
