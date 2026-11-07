<?php

namespace App\Jobs;

use App\Services\Sitemaps\SitemapGeneratorClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshSitemapsForTranslationStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function backoff(): array
    {
        return [10, 30, 120, 300];
    }

    public function handle(SitemapGeneratorClient $client): void
    {
        $client->generate();
    }
}
