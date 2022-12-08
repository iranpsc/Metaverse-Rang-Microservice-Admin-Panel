<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariableChangeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'changer_name',
        'previous_value',
        'current_value',
        'note',
    ];

    public function changeable()
    {
        return $this->morphTo('changeable');
    }
}
