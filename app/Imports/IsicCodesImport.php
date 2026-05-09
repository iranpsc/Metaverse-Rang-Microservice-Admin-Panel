<?php

namespace App\Imports;

use App\Models\IsicCode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class IsicCodesImport implements ToModel, WithChunkReading, WithBatchInserts, ShouldQueue
{
    public function model(array $row)
    {
        return new IsicCode([
            'code' => trim($row[0]),
            'name' => trim($row[1]),
            'verified' => true,
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
