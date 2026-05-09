<?php

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modal extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'sqlite';

    public $timestamps = false;

    public function translation()
    {
        return $this->belongsTo(Translation::class);
    }

    public function tabs()
    {
        return $this->hasMany(Tab::class);
    }

}
