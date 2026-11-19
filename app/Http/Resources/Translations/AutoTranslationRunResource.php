<?php

namespace App\Http\Resources\Translations;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutoTranslationRunResource extends JsonResource
{
    /**
     * Expose the run status and counts used by the language list.
     *
     * @param  Request  $request  Current HTTP request supplied by Laravel.
     * @return array{
     *     id: int,
     *     translation_id: int,
     *     status: 'queued'|'translating'|'publishing'|'completed'|'failed'|'publication_failed',
     *     total: int,
     *     processed: int,
     *     translated: int,
     *     error: string|null
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'translation_id' => $this->translation_id,
            'status' => $this->status,
            'total' => $this->total,
            'processed' => $this->processed,
            'translated' => $this->translated,
            'error' => $this->error,
        ];
    }
}
