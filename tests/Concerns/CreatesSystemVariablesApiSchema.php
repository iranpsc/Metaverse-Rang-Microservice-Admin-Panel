<?php

namespace Tests\Concerns;

use App\Models\SystemVariable;
use App\Models\VariableChangeLog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait CreatesSystemVariablesApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpSystemVariablesApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createSystemVariableChangeLogsTable();
        $this->createSystemVariablesTable();
    }

    private function createSystemVariableChangeLogsTable(): void
    {
        if (Schema::hasTable('variable_change_logs')) {
            return;
        }

        Schema::create('variable_change_logs', function (Blueprint $table) {
            $table->id();
            $table->string('changer_name')->nullable();
            $table->string('previous_value')->nullable();
            $table->string('current_value')->nullable();
            $table->text('note')->nullable();
            $table->string('changeable_type');
            $table->unsignedBigInteger('changeable_id');
            $table->timestamps();
            $table->index(['changeable_type', 'changeable_id']);
        });
    }

    private function createSystemVariablesTable(): void
    {
        if (Schema::hasTable('system_variables')) {
            return;
        }

        Schema::create('system_variables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->decimal('value', 16, 4);
            $table->timestamps();
        });
    }

    protected function createSystemVariable(array $overrides = []): SystemVariable
    {
        $slug = $overrides['slug'] ?? 'sys-'.Str::lower(Str::random(8));

        return SystemVariable::create(array_merge([
            'name' => 'Test System Variable',
            'slug' => $slug,
            'value' => 10.5,
        ], $overrides));
    }

    protected function createSystemVariableChangeLog(SystemVariable $variable, array $overrides = []): VariableChangeLog
    {
        return $variable->changeLogs()->create(array_merge([
            'changer_name' => 'Test Admin',
            'previous_value' => 1,
            'current_value' => 2,
            'note' => 'system note',
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validSystemVariableStorePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Commission Rate',
            'slug' => 'commission-rate-'.Str::lower(Str::random(6)),
            'value' => 12.75,
            'note' => 'optional note',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validSystemVariableUpdatePayload(SystemVariable $variable, array $overrides = []): array
    {
        return array_merge([
            'name' => $variable->name.' Updated',
            'slug' => $variable->slug,
            'value' => 99.25,
            'note' => 'value changed',
        ], $overrides);
    }
}
