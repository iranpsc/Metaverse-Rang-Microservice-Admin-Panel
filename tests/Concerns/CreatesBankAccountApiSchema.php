<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesBankAccountApiSchema
{
    use CreatesKycApiSchema;

    protected function setUpBankAccountApiSchema(): void
    {
        $this->setUpKycApiSchema();
        $this->createBankAccountsTable();
    }

    private function createBankAccountsTable(): void
    {
        if (Schema::hasTable('bank_accounts')) {
            return;
        }

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->nullable();
            $table->string('shaba_num')->nullable();
            $table->string('card_num')->nullable();
            $table->integer('status')->default(0);
            $table->json('errors')->nullable();
            $table->string('bankable_type');
            $table->unsignedBigInteger('bankable_id');
            $table->timestamps();
            $table->index(['bankable_type', 'bankable_id']);
        });
    }
}
