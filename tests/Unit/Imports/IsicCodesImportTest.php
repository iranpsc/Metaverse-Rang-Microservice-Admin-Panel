<?php

namespace Tests\Unit\Imports;

use App\Imports\IsicCodesImport;
use App\Models\IsicCode;
use Tests\TestCase;

class IsicCodesImportTest extends TestCase
{
    public function test_model_maps_row_to_isic_code(): void
    {
        $import = new IsicCodesImport;

        $model = $import->model([' 1234 ', '  Agriculture  ']);

        $this->assertInstanceOf(IsicCode::class, $model);
        $this->assertSame('1234', $model->code);
        $this->assertSame('Agriculture', $model->name);
        $this->assertTrue($model->verified);
    }

    public function test_chunk_and_batch_sizes_are_one_thousand(): void
    {
        $import = new IsicCodesImport;

        $this->assertSame(1000, $import->chunkSize());
        $this->assertSame(1000, $import->batchSize());
    }
}
