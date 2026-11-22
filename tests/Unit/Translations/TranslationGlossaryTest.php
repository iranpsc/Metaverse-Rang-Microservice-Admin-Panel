<?php

namespace Tests\Unit\Translations;

use App\Services\Translations\TranslationGlossary;
use Tests\TestCase;

class TranslationGlossaryTest extends TestCase
{
    /**
     * Check overlapping names, native-script spellings, and existing placeholders.
     */
    public function test_glossary_masks_longer_names_first_and_preserves_existing_markup(): void
    {
        $text = '<b title="MetaRang">ساختمان سطوح شهروندی</b> {{metarang_glossary_0}} و متارنگ';
        [$masked, $names] = (new TranslationGlossary)->protect($text, 'fa', 'ja');

        $this->assertSame('<b title="MetaRang">{{metarang_glossary__0}}</b> {{metarang_glossary_0}} و {{metarang_glossary__1}}', $masked);
        $this->assertSame('<b title="MetaRang">シチズン・レベルズ・ビルディング</b> {{metarang_glossary_0}} و メタラング', strtr($masked, $names));
    }

    /**
     * Check source-script matching and the behavior outside the configured locales.
     */
    public function test_glossary_restores_persian_from_chinese_and_skips_unsupported_destinations(): void
    {
        $glossary = new TranslationGlossary;
        [$masked, $names] = $glossary->protect('梅塔朗', 'zh', 'fa');

        $this->assertSame('متارنگ', strtr($masked, $names));
        [$masked, $names] = $glossary->protect('Metarang', 'en', 'fr');
        $this->assertSame('MetaRang', strtr($masked, $names));
        $this->assertSame(['MetaRang', []], $glossary->protect('MetaRang', 'en', 'sv'));
    }
}
