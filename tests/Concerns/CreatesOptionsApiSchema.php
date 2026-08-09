<?php

namespace Tests\Concerns;

use App\Models\Option;
use App\Models\VariableChangeLog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;

trait CreatesOptionsApiSchema
{
    use CreatesVariablesApiSchema;

    protected function setUpOptionsApiSchema(): void
    {
        $this->setUpVariablesApiSchema();
        $this->createOptionsTable();
    }

    private function createOptionsTable(): void
    {
        if (Schema::hasTable('options')) {
            return;
        }

        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->string('asset');
            $table->bigInteger('amount');
            $table->text('note')->nullable();
            $table->string('code');
            $table->timestamps();
        });
    }

    protected function createOption(array $overrides = []): Option
    {
        return Option::create(array_merge([
            'asset' => 'red',
            'amount' => 10,
            'note' => null,
            'code' => 'PKG-'.uniqid(),
        ], $overrides));
    }

    protected function createOptionWithImage(array $overrides = [], ?string $imageUrl = null): Option
    {
        $option = $this->createOption($overrides);

        $option->image()->create([
            'url' => $imageUrl ?? url('uploads/packages/test.png'),
        ]);

        return $option->fresh(['image', 'priceChangeLogs']);
    }

    protected function createOptionChangeLog(Option $option, array $overrides = []): VariableChangeLog
    {
        return $option->priceChangeLogs()->create(array_merge([
            'changer_name' => 'Test Admin',
            'previous_value' => 10,
            'current_value' => 20,
            'note' => 'option note',
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validOptionStorePayload(array $overrides = []): array
    {
        return array_merge([
            'asset' => 'red',
            'amount' => 5,
            'code' => 'CODE-'.uniqid(),
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validOptionUpdatePayload(array $overrides = []): array
    {
        return array_merge([
            'asset' => 'blue',
            'amount' => 15,
            'code' => 'UPD-'.uniqid(),
            'note' => 'option updated',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validOptionStorePayloadWithImage(array $overrides = []): array
    {
        return $this->validOptionStorePayload(array_merge([
            'image' => UploadedFile::fake()->image('package.png'),
        ], $overrides));
    }
}
