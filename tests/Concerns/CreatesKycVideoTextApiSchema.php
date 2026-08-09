<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesKycVideoTextApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpKycVideoTextApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createKycVerifyTextsTable();
    }

    private function createKycVerifyTextsTable(): void
    {
        if (Schema::hasTable('kyc_verify_texts')) {
            return;
        }

        Schema::create('kyc_verify_texts', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->timestamps();
        });
    }
}
