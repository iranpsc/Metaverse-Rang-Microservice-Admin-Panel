<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariableChangeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'variable_id',
        'option_id',
        'changer_name',
        'previous_price',
        'current_price',
        'note',
    ];

    public function variable()
    {
        return $this->belongsTo(Variable::class);
    }

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
