<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variable extends Model
{
    use HasFactory;
    protected $table = "variables";

    protected $fillable = [
        'asset',
        'price',
        'note'
    ];

    protected $casts =[
        'price' => 'int'
    ];

    public static function getRate($asset) {
        return self::firstWhere('asset', $asset)->price ?? 0;
    }

    public function option() {
        return $this->belongsTo(Option::class);
    }

    public function priceChangeLogs()
    {
        return $this->morphMany(VariableChangeLog::class, 'changeable');
    }
}
