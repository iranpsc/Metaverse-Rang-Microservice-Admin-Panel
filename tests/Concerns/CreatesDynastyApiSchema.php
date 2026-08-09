<?php

namespace Tests\Concerns;

use App\Models\Dynasty\DynastyMessage;
use App\Models\Dynasty\DynastyPermission;
use App\Models\Dynasty\DynastyPrize;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesDynastyApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpDynastyApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createDynastyMessagesTable();
        $this->createDynastyPrizesTable();
        $this->createDynastyPermissionsTable();
    }

    private function createDynastyMessagesTable(): void
    {
        if (Schema::hasTable('dynasty_messages')) {
            return;
        }

        Schema::create('dynasty_messages', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('invitation');
            $table->text('message');
            $table->timestamps();
        });
    }

    private function createDynastyPrizesTable(): void
    {
        if (Schema::hasTable('dynasty_prizes')) {
            return;
        }

        Schema::create('dynasty_prizes', function (Blueprint $table) {
            $table->id();
            $table->string('member');
            $table->float('satisfaction');
            $table->float('introduction_profit_increase');
            $table->float('accumulated_capital_reserve');
            $table->float('data_storage');
            $table->integer('psc');
            $table->timestamps();
        });
    }

    private function createDynastyPermissionsTable(): void
    {
        if (Schema::hasTable('dynasty_permissions')) {
            return;
        }

        Schema::create('dynasty_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('BFR')->default(0);
            $table->boolean('SF')->default(0);
            $table->boolean('W')->default(0);
            $table->boolean('JU')->default(0);
            $table->boolean('DM')->default(0);
            $table->boolean('PIUP')->default(0);
            $table->boolean('PITC')->default(0);
            $table->boolean('PIC')->default(0);
            $table->boolean('ESOO')->default(0);
            $table->boolean('COTB')->default(0);
            $table->timestamps();
        });
    }

    protected function createDynastyMessage(array $overrides = []): DynastyMessage
    {
        return DynastyMessage::create(array_merge([
            'type' => 'requester_confirmation_message',
            'message' => 'Test dynasty message content',
        ], $overrides));
    }

    protected function createDynastyPrize(array $overrides = []): DynastyPrize
    {
        return DynastyPrize::create(array_merge([
            'member' => 'father',
            'satisfaction' => 10,
            'introduction_profit_increase' => 0.25,
            'accumulated_capital_reserve' => 0.10,
            'data_storage' => 0.05,
            'psc' => 100,
        ], $overrides));
    }

    protected function createDynastyPermission(array $overrides = []): DynastyPermission
    {
        return DynastyPermission::create(array_merge([
            'BFR' => 0,
            'SF' => 0,
            'W' => 0,
            'JU' => 0,
            'DM' => 0,
            'PIUP' => 0,
            'PITC' => 0,
            'PIC' => 0,
            'ESOO' => 0,
            'COTB' => 0,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validDynastyMessageStorePayload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'requester_confirmation_message',
            'content' => 'پیام تست سلسله',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validDynastyMessageUpdatePayload(array $overrides = []): array
    {
        return array_merge([
            'content' => 'پیام بروزرسانی شده',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validDynastyPrizeStorePayload(array $overrides = []): array
    {
        return array_merge([
            'member' => 'father',
            'satisfaction' => 50,
            'introduction_profit_increase' => 25,
            'accumulated_capital_reserve' => 10,
            'data_storage' => 5,
            'psc' => 100,
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validDynastyPrizeUpdatePayload(array $overrides = []): array
    {
        return array_merge([
            'satisfaction' => 75,
            'introduction_profit_increase' => 40,
            'accumulated_capital_reserve' => 20,
            'data_storage' => 15,
            'psc' => 200,
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validDynastyPermissionUpdatePayload(array $overrides = []): array
    {
        return array_merge([
            'BFR' => true,
            'SF' => false,
            'W' => true,
            'JU' => false,
            'DM' => true,
            'PIUP' => false,
            'PITC' => true,
            'PIC' => false,
            'ESOO' => true,
            'COTB' => false,
        ], $overrides);
    }
}
