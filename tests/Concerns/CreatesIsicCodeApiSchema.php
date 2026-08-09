<?php

namespace Tests\Concerns;

use App\Models\IsicCode;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesIsicCodeApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpIsicCodeApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createIsicCodesTable();
    }

    private function createIsicCodesTable(): void
    {
        if (Schema::hasTable('isic_codes')) {
            return;
        }

        Schema::create('isic_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('code')->nullable();
            $table->string('name');
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });
    }

    protected function createIsicCode(array $overrides = []): IsicCode
    {
        return IsicCode::factory()->create($overrides);
    }
}
