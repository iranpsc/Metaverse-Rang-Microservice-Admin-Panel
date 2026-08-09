<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SystemVariable extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the casts array.
     *
     * @return array<string, mixed>
     */
    protected function casts()
    {
        return [
            'slug' => 'string',
            'value' => 'float',
        ];
    }

    /**
     * Get the change logs for the variable.
     *
     * @return MorphMany
     */
    public function changeLogs()
    {
        return $this->morphMany(VariableChangeLog::class, 'changeable');
    }
}
