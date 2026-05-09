<?php

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'sqlite';

    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts()
    {
        return [
            'unique_id' => 'integer',
        ];
    }

    protected $attributes = [
        'translation' => null,
    ];

    public function tab()
    {
        return $this->belongsTo(Tab::class);
    }
}
