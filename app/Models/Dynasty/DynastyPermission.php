<?php

namespace App\Models\Dynasty;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynastyPermission extends Model
{
    use HasFactory;

    protected $table = 'dynasty_permissions';

    protected $fillable = [
        'user_id',
        'BFR',
        'SF',
        'W',
        'JU',
        'DM',
        'PIUP',
        'PITC',
        'PIC',
        'ESOO',
        'COTB',
    ];
}
