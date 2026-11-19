<?php

namespace App\Services\Translations;

use RuntimeException;

class OpenAiTranslationException extends RuntimeException
{
    /**
     * Carry a displayable error and its retry policy to the queue worker.
     *
     * @param  string  $message  Provider diagnostic suitable for the admin UI.
     * @param  bool  $retryable  Whether the queue may retry the request.
     */
    public function __construct(string $message, public readonly bool $retryable = false)
    {
        parent::__construct($message);
    }
}
