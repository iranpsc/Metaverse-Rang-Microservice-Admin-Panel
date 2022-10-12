<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Error extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function findErrorByType($type)
    {
        return self::where('type',$type)->get();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
