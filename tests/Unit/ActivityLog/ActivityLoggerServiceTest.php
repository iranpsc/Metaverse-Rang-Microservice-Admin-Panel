<?php

namespace Tests\Unit\ActivityLog;

use App\Models\User;
use App\Services\ActivityLoggerService;
use Illuminate\Database\Eloquent\Model;
use ReflectionMethod;
use Tests\TestCase;

class ActivityLoggerServiceTest extends TestCase
{
    public function test_morph_subject_and_description_helpers_handle_key_types(): void
    {
        $canUse = new ReflectionMethod(ActivityLoggerService::class, 'canUseAsMorphSubject');
        $canUse->setAccessible(true);
        $describe = new ReflectionMethod(ActivityLoggerService::class, 'buildModelDescription');
        $describe->setAccessible(true);

        $this->assertFalse($canUse->invoke(null, new User));
        $this->assertTrue($canUse->invoke(null, (new User)->forceFill(['id' => 12])));
        $this->assertTrue($canUse->invoke(null, (new User)->forceFill(['id' => '12'])));
        $stringKeyModel = new class extends Model
        {
            protected $keyType = 'string';

            public $incrementing = false;
        };
        $this->assertFalse($canUse->invoke(null, $stringKeyModel->forceFill(['id' => 'abc'])));

        $model = (new class extends Model
        {
            protected $table = 'synthetic';
        })->forceFill(['id' => 3]);

        $this->assertStringEndsWith('#3 created', $describe->invoke(null, $model, 'created'));
        $this->assertStringEndsWith('#3 archived', $describe->invoke(null, $model, 'archived'));
    }
}
