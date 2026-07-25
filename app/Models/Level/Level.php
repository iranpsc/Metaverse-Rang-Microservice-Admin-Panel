<?php

namespace App\Models\Level;

use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Level extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function generalInfo(): HasOne
    {
        return $this->hasOne(LevelGeneralInfo::class);
    }

    public function licenses(): HasOne
    {
        return $this->hasOne(LevelLicense::class);
    }

    public function gem(): HasOne
    {
        return $this->hasOne(LevelGem::class);
    }

    public function prize(): HasOne
    {
        return $this->hasOne(LevelPrize::class);
    }

    public function gift(): HasOne
    {
        return $this->hasOne(LevelGift::class);
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'level_user')->withTimestamps();
    }

    public function scopeOrderByNumericScore($query, string $direction = 'asc')
    {
        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

        return $query->orderByRaw("CAST(score AS UNSIGNED) {$direction}");
    }

    public function scopeWhereNumericScore($query, string $operator, int $value)
    {
        $allowedOperators = ['>', '>=', '<', '<=', '='];

        if (!in_array($operator, $allowedOperators, true)) {
            throw new \InvalidArgumentException("Unsupported operator [{$operator}]");
        }

        return $query->whereRaw("CAST(score AS UNSIGNED) {$operator} ?", [$value]);
    }

    public function getNumericScoreAttribute(): int
    {
        return (int) $this->score;
    }
}
