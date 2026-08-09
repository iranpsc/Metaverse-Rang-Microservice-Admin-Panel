<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesTicketsApiSchema
{
    use CreatesKycApiSchema;

    protected function setUpTicketsApiSchema(): void
    {
        $this->setUpKycApiSchema();
        $this->createTicketsTable();
        $this->createTicketResponsesTable();
    }

    private function createTicketsTable(): void
    {
        if (Schema::hasTable('tickets')) {
            return;
        }

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('reciever_id')->nullable();
            $table->string('attachment')->nullable();
            $table->integer('status')->default(0);
            $table->string('department')->nullable();
            $table->integer('importance')->default(0);
            $table->timestamps();
        });
    }

    private function createTicketResponsesTable(): void
    {
        if (Schema::hasTable('ticket_responses')) {
            return;
        }

        Schema::create('ticket_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->text('response')->nullable();
            $table->string('attachment')->nullable();
            $table->string('responser_name')->nullable();
            $table->unsignedBigInteger('responser_id')->nullable();
            $table->timestamps();
        });
    }
}
