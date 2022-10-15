<?php

namespace App\Models\MapManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Polygon extends Model
{
    use HasFactory;

    protected $table = 'polygons';

    protected $fillable = [
        'name',
        'publish_date',
        'publisher_name',
        'polygon_count',
        'total_area',
        'first_id',
        'last_id',
        'status',
        'karbari',
        'fileName'
    ];
}
