<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemVariable extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function changeLogs()
    {
        return $this->morphMany(VariableChangeLog::class, 'changeable');
    }
}
