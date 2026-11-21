<?php

namespace App\Services\Translations;

use App\Models\Translations\Translation;

interface TranslationProvider
{
    /**
     * Return the maximum number of fields submitted in one queue step.
     *
     * @return int Positive provider-specific field batch size.
     */
    public function batchSize(): int;

    /**
     * Translate a bounded batch and validate provider output before persistence.
     *
     * @param  Translation  $destination  Destination locale.
     * @param  array<int, array{id: string, source: string, text: string, modal: string, tab: string}>  $items  Source strings.
     * @return array<int, string> Validated translation text keyed by the shared field ID.
     *
     * @throws TranslationProviderException When the provider rejects or corrupts a result.
     */
    public function translate(Translation $destination, array $items): array;
}
