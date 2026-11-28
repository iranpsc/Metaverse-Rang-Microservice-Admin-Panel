<?php

namespace App\Jobs;

use App\Services\Translations\AutoTranslationService;
use App\Services\Translations\TranslationProviderException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessAutoTranslation implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 80;

    public array $backoff = [10, 30];

    /**
     * Identify the run and the exact step this job is authorized to process.
     *
     * @param  int  $runId  Run identifier stored in the queue payload.
     * @param  int  $step  Step number to process.
     * @param  int  $capacityRetries  Capacity retries used for this batch.
     */
    public function __construct(public readonly int $runId, public readonly int $step, public int $capacityRetries = 0) {}

    /**
     * Process a step and schedule delayed continuation when the provider is paused.
     *
     * @param  AutoTranslationService  $service  Run service injected by the queue worker.
     * @return void Retryable failures propagate to Laravel's queue worker.
     */
    public function handle(AutoTranslationService $service): void
    {
        try {
            $service->process($this->runId, $this->step);
        } catch (TranslationProviderException $exception) {
            if ($exception->resumeAt !== null) {
                $capacityRetries = ($this->capacityRetries ?? 0) + (int) $exception->capacityFailure;
                if ($capacityRetries <= 2) {
                    $service->defer($this->runId, $this->step, $exception, $capacityRetries);

                    return;
                }
            }

            if (! $exception->retryable) {
                $this->fail($exception);

                return;
            }

            $service->recordProviderError($this->runId, $this->step, $exception->getMessage());

            throw $exception;
        }
    }

    /**
     * Persist the terminal state for the step that failed.
     *
     * @param  Throwable|null  $exception  Failure reported by Laravel's queue worker.
     * @return void Updates the run through the translation service.
     */
    public function failed(?Throwable $exception): void
    {
        $error = $exception instanceof TranslationProviderException ? $exception->getMessage() : null;
        app(AutoTranslationService::class)->fail($this->runId, $this->step, $error);
    }
}
