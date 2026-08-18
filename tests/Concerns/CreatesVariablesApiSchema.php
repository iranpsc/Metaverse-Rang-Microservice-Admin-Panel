<?php

namespace Tests\Concerns;

use App\Models\Variable;
use App\Models\VariableChangeLog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

trait CreatesVariablesApiSchema
{
    use CreatesCitizensApiSchema;

    protected function setUpVariablesApiSchema(): void
    {
        $this->setUpCitizensApiSchema();
        $this->createVariableChangeLogsTable();
        $this->createVariablesTable();
        Storage::fake('public');
    }

    protected function createVariableChangeLogsTable(): void
    {
        if (Schema::hasTable('variable_change_logs')) {
            return;
        }

        Schema::create('variable_change_logs', function (Blueprint $table) {
            $table->id();
            $table->string('changer_name');
            $table->string('previous_value')->nullable();
            $table->string('current_value')->nullable();
            $table->text('note')->nullable();
            $table->string('changeable_type');
            $table->unsignedBigInteger('changeable_id');
            $table->timestamps();
            $table->index(['changeable_type', 'changeable_id']);
        });
    }

    private function createVariablesTable(): void
    {
        if (Schema::hasTable('variables')) {
            return;
        }

        Schema::create('variables', function (Blueprint $table) {
            $table->id();
            $table->string('asset');
            $table->bigInteger('price');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    protected function createVariable(array $overrides = []): Variable
    {
        return Variable::create(array_merge([
            'asset' => 'red',
            'price' => 1000,
            'note' => null,
        ], $overrides));
    }

    protected function createVariableWithImage(array $overrides = [], ?string $imageUrl = null): Variable
    {
        $variable = $this->createVariable($overrides);

        $variable->image()->create([
            'url' => $imageUrl ?? url('uploads/variables/test.png'),
        ]);

        return $variable->fresh(['image', 'priceChangeLogs']);
    }

    protected function createVariableChangeLog(Variable $variable, array $overrides = []): VariableChangeLog
    {
        return $variable->priceChangeLogs()->create(array_merge([
            'changer_name' => 'Test Admin',
            'previous_value' => 100,
            'current_value' => 200,
            'note' => 'test note',
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validVariableStorePayload(array $overrides = []): array
    {
        return array_merge([
            'asset' => 'red',
            'price' => 1500,
            'image' => UploadedFile::fake()->image('variable.jpg'),
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validVariableUpdatePayload(array $overrides = []): array
    {
        return array_merge([
            'price' => 2500,
            'note' => 'updated note',
        ], $overrides);
    }
}
