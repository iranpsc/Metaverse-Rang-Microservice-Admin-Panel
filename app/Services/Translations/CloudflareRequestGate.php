<?php

namespace App\Services\Translations;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Sleep;

class CloudflareRequestGate
{
    private const REQUEST_SPACING_MICROSECONDS = 200_000;

    /**
     * Reserve one request against the shared account budget.
     *
     * @return void Throws a deferred provider error when the account is paused or at its request limit.
     */
    public function reserve(): void
    {
        $key = $this->key();
        try {
            Cache::lock($key.':lock', 10)->block(2, function () use ($key) {
                $pause = Cache::get($key.':pause');
                if ($pause && $pause['until'] > now()->timestamp) {
                    throw new TranslationProviderException($pause['message'], resumeAt: $pause['until']);
                }

                $limit = max(1, min(360, (int) config('services.cloudflare.translation_requests_per_minute')));
                if (RateLimiter::tooManyAttempts($key, $limit)) {
                    throw new TranslationProviderException(
                        'ارسال درخواست‌های Cloudflare موقتاً متوقف شده است.',
                        resumeAt: now()->timestamp + max(1, RateLimiter::availableIn($key)),
                    );
                }

                $elapsed = (int) (microtime(true) * 1_000_000) - (int) Cache::get($key.':last-request', 0);
                $wait = self::REQUEST_SPACING_MICROSECONDS - max(0, $elapsed);
                if ($wait > 0) {
                    Sleep::usleep($wait);
                }

                RateLimiter::hit($key);
                Cache::put($key.':last-request', (int) (microtime(true) * 1_000_000), 2);
            });
        } catch (LockTimeoutException) {
            throw new TranslationProviderException('صف درخواست‌های Cloudflare مشغول است.', resumeAt: now()->timestamp + 2);
        }
    }

    /**
     * Stop subsequent requests on this account until the provider can accept work again.
     *
     * @param  int  $until  Unix timestamp when requests can resume.
     * @param  string  $message  Provider diagnostic shown during the pause.
     * @return void Keeps the latest known resume time.
     */
    public function pause(int $until, string $message): void
    {
        $key = $this->key();
        Cache::lock($key.':lock', 10)->block(2, function () use ($key, $until, $message) {
            $current = Cache::get($key.':pause');
            if (($current['until'] ?? 0) < $until) {
                Cache::put($key.':pause', ['until' => $until, 'message' => $message], $until - now()->timestamp);
            }
        });
    }

    /**
     * Build a cache key shared by runs using the same Cloudflare account.
     *
     * @return string Account-scoped cache key.
     */
    private function key(): string
    {
        return 'auto-translations:cloudflare:'.hash('sha256', (string) config('services.cloudflare.account_id'));
    }
}
