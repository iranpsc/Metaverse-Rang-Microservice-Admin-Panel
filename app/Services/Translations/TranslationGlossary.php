<?php

namespace App\Services\Translations;

class TranslationGlossary
{
    private const LATIN_LOCALES = ['en', 'fr', 'de', 'es', 'pt', 'it', 'tr', 'id'];

    /**
     * Replace glossary names with placeholders the translation providers preserve.
     *
     * @param  string  $text  Original field text.
     * @param  string  $source  Source language code.
     * @param  string  $destination  Destination language code.
     * @return array{string, array<string, string>} Masked text and placeholder-to-name map.
     */
    public function protect(string $text, string $source, string $destination): array
    {
        $destination = in_array($destination, self::LATIN_LOCALES, true) ? 'en' : $destination;
        $names = [];
        foreach (config('translation-glossary') as $term) {
            if (isset($term[$destination])) {
                foreach ([$term['fa'], $term['en'], $term[$source] ?? $term['en']] as $name) {
                    $names[$name] = $term[$destination];
                }

                if ($term['en'] === 'MetaRang') {
                    $names['Metarang'] = $term[$destination];
                }
            }
        }

        if ($names === []) {
            return [$text, []];
        }

        uksort($names, fn (string $left, string $right) => strlen($right) <=> strlen($left));
        $alternatives = array_map(fn (string $name) => preg_quote($name, '~'), array_keys($names));
        $pattern = '~(?:\{\{[^{}]+\}\}|\{[\w.-]+\}|(?<![\w:]):[a-zA-Z_]\w*|%[a-zA-Z_]\w*%|<[a-zA-Z/!][^<>]*>)(*SKIP)(*F)|'
            .'(?<![\p{Latin}\p{Arabic}\p{Cyrillic}\p{Devanagari}\p{N}])(?:'.implode('|', $alternatives).')'
            .'(?![\p{Latin}\p{Arabic}\p{Cyrillic}\p{Devanagari}\p{N}])~u';
        $prefix = 'metarang_glossary_';
        while (str_contains($text, '{{'.$prefix)) {
            $prefix .= '_';
        }

        $replacements = [];
        $masked = preg_replace_callback($pattern, function (array $match) use ($names, $prefix, &$replacements): string {
            $placeholder = '{{'.$prefix.count($replacements).'}}';
            $replacements[$placeholder] = $names[$match[0]];

            return $placeholder;
        }, $text);

        return [$masked ?? $text, $replacements];
    }
}
