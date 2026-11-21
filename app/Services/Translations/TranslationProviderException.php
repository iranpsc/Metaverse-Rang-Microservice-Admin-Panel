<?php

namespace App\Services\Translations;

use RuntimeException;

class TranslationProviderException extends RuntimeException
{
    /**
     * Carry a displayable provider error and its retry policy to the queue worker.
     *
     * @param  string  $message  Provider diagnostic suitable for the admin UI.
     * @param  bool  $retryable  Whether the queue may retry the request.
     * @param  array<int, string>  $results  Completed field translations that must be committed before handling the error.
     */
    public function __construct(string $message, public readonly bool $retryable = false, public readonly array $results = [])
    {
        parent::__construct($message);
    }
}
