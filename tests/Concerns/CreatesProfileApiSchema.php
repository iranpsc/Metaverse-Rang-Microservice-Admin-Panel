<?php

namespace Tests\Concerns;

use App\Models\Admin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

trait CreatesProfileApiSchema
{
    use CreatesVerificationApiSchema;

    protected function setUpProfileApiSchema(): void
    {
        config()->set('activitylog.enabled', false);

        $this->setUpVerificationApiSchema();
    }

    protected function setUpProfileStorage(): void
    {
        Storage::fake('public');
    }

    protected function seedPhoneVerificationSession(Admin $admin, int $durationMinutes = 15): void
    {
        Cache::put('phone.verification.'.$admin->id, [
            'verified_at' => now()->timestamp,
            'duration_minutes' => $durationMinutes,
        ], now()->addMinutes($durationMinutes));
    }
}
