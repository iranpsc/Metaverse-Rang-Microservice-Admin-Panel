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
     * @param  int|null  $resumeAt  Unix timestamp for a delayed continuation.
     * @param  bool  $capacityFailure  Whether this request used a capacity retry.
     */
    public function __construct(
        string $message,
        public readonly bool $retryable = false,
        public readonly array $results = [],
        public readonly ?int $resumeAt = null,
        public readonly bool $capacityFailure = false,
    ) {
        parent::__construct($message);
    }
}
