<?php

namespace Tests\Unit\Imports;

use App\Imports\QuestionFileImport;
use Tests\TestCase;

class QuestionFileImportTest extends TestCase
{
    public function test_array_returns_rows_unchanged(): void
    {
        $rows = [
            ['Question', 'Answer'],
            ['What is 2 + 2?', '4'],
        ];

        $this->assertSame($rows, (new QuestionFileImport)->array($rows));
    }
}
