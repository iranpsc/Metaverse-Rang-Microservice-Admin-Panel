<?php

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tab extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'sqlite';

    public $timestamps = false;

    public function fields()
    {
        return $this->hasMany(Field::class);
    }

    public function modal()
    {
        return $this->belongsTo(Modal::class);
    }
}
