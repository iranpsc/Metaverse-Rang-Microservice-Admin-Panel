<?php

namespace App\Jobs;

use App\Services\Challenge\ChallengeQuestionImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportChallengeQuestions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $data
    ) {}

    public function handle(ChallengeQuestionImportService $service): void
    {
        $service->import($this->data);
    }
}
