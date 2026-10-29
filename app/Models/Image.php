<?php

namespace App\Models;

use App\Models\Level\Level;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['url'];

    public function imageable()
    {
        // Some historical records contain App//Models//Levels//Level. Eloquent
        // reads morph types from the raw attributes, so normalize before morphTo
        // tries to instantiate the related model.
        $rawType = $this->getRawOriginal('imageable_type');
        $normalizedType = $rawType === null
            ? null
            : str_replace('\\\\', '\\', str_replace('/', '\\', $rawType));

        if ($normalizedType === 'App\\Models\\Levels\\Level') {
            $this->setAttribute('imageable_type', Level::class);
        }

        return $this->morphTo();
    }
}
