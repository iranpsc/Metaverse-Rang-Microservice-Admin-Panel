<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ref_id',
        'card_pan',
        'gate_way',
        'product',
        'amount',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch($query, $searchTerm)
    {
        $term = '%'.trim($searchTerm).'%';

        return $query->where(function ($q) use ($term) {
            $q->where('ref_id', 'like', $term)
                ->orWhereHas('user', function ($userQuery) use ($term) {
                    $userQuery->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term);
                });
        });
    }

    public function scopeOfProduct($query, $product)
    {
        return $query->where('product', $product);
    }

    public function getTitle()
    {
        return match ($this->product) {
            'irr' => 'ریال',
            'psc' => 'PSC',
            'red' => 'رنگ قرمز',
            'blue' => 'رنگ آبی',
            'yellow' => 'رنگ زرد',
        };
    }
}
