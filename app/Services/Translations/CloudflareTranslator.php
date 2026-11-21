<?php

namespace App\Services\Translations;

use App\Models\Translations\Translation;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CloudflareTranslator implements TranslationProvider
{
    private const PROTECTED_TEXT = '~(\{\{[^{}]+\}\}|\{[\w.-]+\}|(?<![\w:]):[a-z_]\w*|%[a-z_]\w*%|%%|%(?:\d+\$)?[sdif]|<[a-z/!][^<>]*>|\b(?:MetaRang|VOD|PSC|PSD|Rial)\b)~iu';

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

        foreach ($items as $id => $item) {
            try {
                $results[$id] = $this->translateItem($destination, $item, $id);
            } catch (TranslationProviderException $exception) {
                throw new TranslationProviderException($exception->getMessage(), $exception->retryable, $results);
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
     * @return string Reconstructed translated field.
     */
    private function translateItem(Translation $destination, array $item, int $id): string
    {
        $parts = preg_split(self::PROTECTED_TEXT, $item['text'], -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            throw new TranslationProviderException("Cloudflare Workers AI could not read the text for field {$id}.");
        }

        foreach ($parts as $index => $part) {
            if ($index % 2 !== 0 || ! preg_match('/\p{L}/u', $part)) {
                continue;
            }

            $parts[$index] = $this->translateSegment($destination, $item['source'], $part, $id);
        }

        $text = implode('', $parts);
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

        try {
            $response = Http::withToken(config('services.cloudflare.api_token'))
                ->acceptJson()
                ->withoutRedirecting()
                ->connectTimeout(5)
                ->timeout(15)
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
            $detail = $this->safeError($response->json('errors.0.message'));
            $retryable = $status === 408 || $response->serverError();

            throw new TranslationProviderException("Cloudflare Workers AI (HTTP {$status})".($detail ? ": {$detail}" : ''), $retryable);
        }

        $text = $response->json('result.translated_text');
        if (! is_string($text) || trim($text) === '') {
            throw new TranslationProviderException("Cloudflare Workers AI returned an empty or invalid translation for field {$id}.");
        }

        return trim($text);
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
