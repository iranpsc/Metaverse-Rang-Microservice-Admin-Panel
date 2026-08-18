<?php

namespace Tests\Concerns;

use App\Models\Admin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

trait CreatesVerificationApiSchema
{
    use CreatesAuthApiSchema;

    protected function setUpVerificationApiSchema(): void
    {
        $this->setUpAuthApiSchema();
    }

    protected function seedVerificationCode(Admin $admin, string $code = '123456'): void
    {
        Cache::put(
            'verify.code.'.$admin->id,
            Hash::make($code),
            now()->addMinutes(1)
        );
    }

    protected function putSmsCooldown(Admin $admin, int $remainingSeconds): void
    {
        Cache::put(
            'verify.sms.cooldown.'.$admin->id,
            now()->addSeconds($remainingSeconds)->timestamp,
            $remainingSeconds
        );
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function withProductionEnvironment(callable $callback)
    {
        $this->app->detectEnvironment(fn () => 'production');

        try {
            return $callback();
        } finally {
            $this->app->detectEnvironment(fn () => 'testing');
        }
    }
}
