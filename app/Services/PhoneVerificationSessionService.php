<?php

namespace App\Services;

class PhoneVerificationSessionService
{
    private const SESSION_KEY_VERIFIED_AT = 'phone_verified_at';

    private const SESSION_KEY_DURATION = 'phone_verification_duration_minutes';

    public function isVerified(): bool
    {
        if (! app()->environment('production')) {
            return true;
        }

        $verifiedAt = session(self::SESSION_KEY_VERIFIED_AT);

        if (! $verifiedAt) {
            return false;
        }

        return $this->remainingSeconds() > 0;
    }

    public function confirm(int $durationMinutes): void
    {
        session([
            self::SESSION_KEY_VERIFIED_AT => now()->timestamp,
            self::SESSION_KEY_DURATION => $durationMinutes,
        ]);
    }

    public function clear(): void
    {
        session()->forget([
            self::SESSION_KEY_VERIFIED_AT,
            self::SESSION_KEY_DURATION,
        ]);
    }

    public function getStatus(): array
    {
        if (! app()->environment('production')) {
            return [
                'verified' => true,
                'expires_at' => null,
                'remaining_seconds' => null,
                'duration_minutes' => null,
            ];
        }

        $verifiedAt = session(self::SESSION_KEY_VERIFIED_AT);
        $durationMinutes = (int) session(
            self::SESSION_KEY_DURATION,
            config('phone_verification.default_duration_minutes')
        );

        if (! $verifiedAt) {
            return [
                'verified' => false,
                'expires_at' => null,
                'remaining_seconds' => 0,
                'duration_minutes' => null,
            ];
        }

        $remainingSeconds = $this->remainingSeconds();
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

    private function remainingSeconds(): int
    {
        $verifiedAt = session(self::SESSION_KEY_VERIFIED_AT);

        if (! $verifiedAt) {
            return 0;
        }

        $durationMinutes = (int) session(
            self::SESSION_KEY_DURATION,
            config('phone_verification.default_duration_minutes')
        );

        $expiresAt = (int) $verifiedAt + ($durationMinutes * 60);

        return $expiresAt - now()->timestamp;
    }
}
