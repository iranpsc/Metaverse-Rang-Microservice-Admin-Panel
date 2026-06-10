<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PhoneVerificationSessionService
{
    private function cacheKey(?int $adminId = null): ?string
    {
        $adminId ??= Auth::guard('admin')->id();

        if (! $adminId) {
            return null;
        }

        return 'phone.verification.'.$adminId;
    }

    public function isEnabled(): bool
    {
        return app()->environment('production');
    }

    public function isVerified(): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        return $this->remainingSeconds() > 0;
    }

    public function confirm(int $durationMinutes): void
    {
        $key = $this->cacheKey();

        if (! $key) {
            return;
        }

        $durationMinutes = $this->clampDuration($durationMinutes);

        Cache::put($key, [
            'verified_at' => now()->timestamp,
            'duration_minutes' => $durationMinutes,
        ], now()->addMinutes($durationMinutes));
    }

    public function clear(?int $adminId = null): void
    {
        $key = $this->cacheKey($adminId);

        if ($key) {
            Cache::forget($key);
        }
    }

    public function getStatus(): array
    {
        if (! $this->isEnabled()) {
            return [
                'verified' => true,
                'expires_at' => null,
                'remaining_seconds' => null,
                'duration_minutes' => null,
            ];
        }

        $payload = $this->getPayload();

        if (! $payload) {
            return [
                'verified' => false,
                'expires_at' => null,
                'remaining_seconds' => 0,
                'duration_minutes' => null,
            ];
        }

        $remainingSeconds = $this->remainingSeconds();
        $durationMinutes = (int) $payload['duration_minutes'];
        $verifiedAt = (int) $payload['verified_at'];
        $expiresAt = $remainingSeconds > 0
            ? now()->setTimestamp($verifiedAt)->addMinutes($durationMinutes)->toIso8601String()
            : null;

        return [
            'verified' => $remainingSeconds > 0,
            'expires_at' => $expiresAt,
            'remaining_seconds' => max(0, $remainingSeconds),
            'duration_minutes' => $durationMinutes,
        ];
    }

    public function clampDuration(int $durationMinutes): int
    {
        $min = config('phone_verification.min_duration_minutes');
        $max = config('phone_verification.max_duration_minutes');

        return max($min, min($max, $durationMinutes));
    }

    private function getPayload(): ?array
    {
        $key = $this->cacheKey();

        if (! $key) {
            return null;
        }

        $payload = Cache::get($key);

        return is_array($payload) ? $payload : null;
    }

    private function remainingSeconds(): int
    {
        $payload = $this->getPayload();

        if (! $payload) {
            return 0;
        }

        $verifiedAt = (int) $payload['verified_at'];
        $durationMinutes = (int) $payload['duration_minutes'];
        $expiresAt = $verifiedAt + ($durationMinutes * 60);

        return $expiresAt - now()->timestamp;
    }
}
