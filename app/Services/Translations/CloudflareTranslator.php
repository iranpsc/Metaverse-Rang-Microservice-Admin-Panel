<?php

namespace App\Services\Translations;

use App\Models\Translations\Translation;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CloudflareTranslator implements TranslationProvider
{
    // Leave room in the 80-second worker timeout for the last HTTP call, cache operations, and checkpointing.
    private const REQUEST_START_WINDOW_SECONDS = 55;

    private const REQUEST_TIMEOUT_SECONDS = 15;

    private const PROTECTED_TEXT = '~(\{\{[^{}]+\}\}|\{[\w.-]+\}|(?<![\w:]):[a-z_]\w*|%[a-z_]\w*%|%%|%(?:\d+\$)?[sdif]|<[a-z/!][^<>]*>|\b(?:MetaRang|VOD|PSC|PSD|Rial)\b)~iu';

    /**
     * Share the request budget and cooldown across queue jobs.
     *
     * @param  CloudflareRequestGate  $gate  Account-scoped limiter.
     */
    public function __construct(private readonly CloudflareRequestGate $gate) {}

    /**
     * Return the field limit used by the Cloudflare free-tier queue step.
     *
     * @return int Positive number of fields in one Cloudflare batch.
     */
    public function batchSize(): int
    {
        return max(1, (int) config('services.cloudflare.translation_batch_size'));
    }

    /**
     * Translate a small batch and retain completed fields if Cloudflare stops accepting requests.
     *
     * @param  Translation  $destination  Locale whose code is sent as the target language.
     * @param  array<int, array{id: string, source: string, text: string, modal: string, tab: string}>  $items  Source strings and screen context.
     * @return array<int, string> Validated translation text keyed by the shared field ID.
     *
     * @throws TranslationProviderException For a rejected request, connection failure, or invalid response.
     */
    public function translate(Translation $destination, array $items): array
    {
        $results = [];
        $requestDeadline = now()->timestamp + self::REQUEST_START_WINDOW_SECONDS;

        foreach ($items as $id => $item) {
            try {
                $results[$id] = $this->translateItem($destination, $item, $id, $requestDeadline);
            } catch (TranslationProviderException $exception) {
                throw new TranslationProviderException(
                    $exception->getMessage(),
                    $exception->retryable,
                    $results,
                    $exception->resumeAt,
                    $exception->capacityFailure,
                );
            }
        }

        return $results;
    }

    /**
     * Translate the non-protected fragments of one field and reconstruct the original protected tokens.
     *
     * @param  Translation  $destination  Destination locale.
     * @param  array{id: string, source: string, text: string, modal: string, tab: string}  $item  Source field.
     * @param  int  $id  Shared field ID for diagnostics.
     * @param  int  $requestDeadline  Unix timestamp after which no new HTTP request should start.
     * @return string Reconstructed translated field.
     */
    private function translateItem(Translation $destination, array $item, int $id, int $requestDeadline): string
    {
        $parts = preg_split(self::PROTECTED_TEXT, $item['text'], -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            throw new TranslationProviderException("Cloudflare Workers AI could not read the text for field {$id}.");
        }

        $key = 'auto-translations:cloudflare:field:'.hash('sha256', serialize([
            config('services.cloudflare.account_id'),
            $this->endpoint(),
            $destination->id,
            $destination->code,
            $id,
            $item['source'],
            $item['text'],
        ]));
        $segments = Cache::get($key, []);
        try {
            foreach ($parts as $index => $part) {
                if ($index % 2 !== 0 || isset($segments[$index]) || ! preg_match('/\p{L}/u', $part)) {
                    continue;
                }

                if (now()->timestamp >= $requestDeadline) {
                    throw new TranslationProviderException(
                        'ترجمه Cloudflare در مرحله بعد ادامه پیدا می‌کند.',
                        resumeAt: now()->timestamp + 1,
                    );
                }

                $segments[$index] = $this->translateSegment($destination, $item['source'], $part, $id);
            }
        } catch (TranslationProviderException $exception) {
            if ($exception->resumeAt !== null && $segments !== []) {
                Cache::put($key, $segments, max(1, $exception->resumeAt - now()->timestamp) + 3600);
            }

            throw $exception;
        }

        $text = implode('', array_replace($parts, $segments));
        Cache::forget($key);
        if ($this->protectedTokens($item['text']) !== $this->protectedTokens($text)) {
            throw new TranslationProviderException("Cloudflare Workers AI changed protected text in field {$id}.");
        }

        return $text;
    }

    /**
     * Send one unprotected text fragment to Cloudflare Workers AI.
     *
     * @param  Translation  $destination  Destination locale.
     * @param  string  $source  Source locale code.
     * @param  string  $part  Fragment including original whitespace.
     * @param  int  $id  Shared field ID for diagnostics.
     * @return string Translated fragment with its surrounding whitespace restored.
     */
    private function translateSegment(Translation $destination, string $source, string $part, int $id): string
    {
        preg_match('/^(\s*).*?(\s*)$/suD', $part, $spacing);

        $this->gate->reserve();

        try {
            $response = Http::withToken(config('services.cloudflare.api_token'))
                ->acceptJson()
                ->withoutRedirecting()
                ->connectTimeout(5)
                ->timeout(self::REQUEST_TIMEOUT_SECONDS)
                ->post($this->endpoint(), [
                    'text' => trim($part),
                    'source_lang' => $source,
                    'target_lang' => $destination->code,
                ]);
        } catch (ConnectionException) {
            throw new TranslationProviderException('اتصال به Cloudflare برقرار نشد یا زمان پاسخ‌گویی پایان یافت.', retryable: true);
        }

        return $spacing[1].$this->translatedText($response, $id).$spacing[2];
    }

    /**
     * Read the translated fragment or classify the provider failure for the queue.
     *
     * @param  Response  $response  Cloudflare HTTP response.
     * @param  int  $id  Shared field ID shown in diagnostics.
     * @return string Translated fragment without surrounding whitespace.
     */
    private function translatedText(Response $response, int $id): string
    {
        if (! $response->successful()) {
            $status = $response->status();
            $code = filter_var($response->json('errors.0.code'), FILTER_VALIDATE_INT);
            $detail = $this->safeError($response->json('errors.0.message'));
            $label = $code === false ? "HTTP {$status}" : "HTTP {$status}, code {$code}";
            $message = "Cloudflare Workers AI ({$label})".($detail ? ": {$detail}" : '');

            if ($status === 429 && in_array($code, [3036, 3040], true)) {
                $resumeAt = $code === 3036
                    ? now('UTC')->addDay()->startOfDay()->timestamp
                    : $this->capacityResumeAt($response);
                try {
                    $this->gate->pause($resumeAt, $message);
                } catch (LockTimeoutException $exception) {
                    report($exception);
                }

                throw new TranslationProviderException($message, resumeAt: $resumeAt, capacityFailure: $code === 3040);
            }

            throw new TranslationProviderException($message, $status === 408 || $response->serverError());
        }

        $text = $response->json('result.translated_text');
        if (! is_string($text) || trim($text) === '') {
            throw new TranslationProviderException("Cloudflare Workers AI returned an empty or invalid translation for field {$id}.");
        }

        return trim($text);
    }

    /**
     * Read the upstream Retry-After delay or use a 30-second cooldown.
     *
     * @param  Response  $response  Cloudflare capacity response.
     * @return int Unix timestamp for the next attempt.
     */
    private function capacityResumeAt(Response $response): int
    {
        $header = trim((string) $response->header('Retry-After'));
        $now = now()->timestamp;
        if (ctype_digit($header)) {
            return $now + max(1, (int) $header);
        }

        $date = $header === '' ? false : strtotime($header, $now);

        return $date === false ? $now + 30 : max($now + 1, $date);
    }

    /**
     * Build the Cloudflare Workers AI translation endpoint.
     *
     * @return string Fully qualified model endpoint.
     */
    private function endpoint(): string
    {
        return rtrim((string) config('services.cloudflare.endpoint'), '/').'/'.config('services.cloudflare.translation_model');
    }

    /**
     * Redact credentials and bound the length of a provider diagnostic shown to the admin.
     *
     * @param  mixed  $message  Error detail returned by Cloudflare.
     * @return string|null Displayable diagnostic, when present.
     */
    private function safeError(mixed $message): ?string
    {
        if (! is_string($message) || trim($message) === '') {
            return null;
        }

        $message = str_replace((string) config('services.cloudflare.api_token'), '[redacted]', $message);

        return Str::limit(Str::squish($message), 800);
    }

    /**
     * Return recognized terms, placeholders, and markup in a stable comparison order.
     *
     * @param  string  $text  Source or translated UI string.
     * @return list<string> Matched terms, placeholders, and markup, including repeated tokens.
     */
    private function protectedTokens(string $text): array
    {
        preg_match_all(self::PROTECTED_TEXT, $text, $matches);
        $tokens = $matches[0];
        sort($tokens, SORT_STRING);

        return $tokens;
    }
}
