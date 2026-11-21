<?php

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class AutoTranslationRun extends Model
{
    public const QUEUED = 'queued';

    public const TRANSLATING = 'translating';

    public const PUBLISHING = 'publishing';

    public const COMPLETED = 'completed';

    public const FAILED = 'failed';

    public const PUBLICATION_FAILED = 'publication_failed';

    public const CANCELLED = 'cancelled';

    protected $connection = 'sqlite';

    protected $guarded = [];

    /**
     * Decode the stored actor and staged translation data as arrays.
     *
     * @return array{actor: 'array', items: 'array', results: 'array'}
     */
    protected function casts(): array
    {
        return [
            'actor' => 'array',
            'items' => 'array',
            'results' => 'array',
        ];
    }
}
