<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class AdminTabSessionService
{
    public function tokenFromRequest(Request $request): ?PersonalAccessToken
    {
        $bearerToken = $request->bearerToken();

        if ($bearerToken) {
            return PersonalAccessToken::findToken($bearerToken);
        }

        $token = $request->user()?->currentAccessToken();

        return $token instanceof PersonalAccessToken ? $token : null;
    }

    public function claim(PersonalAccessToken $token, string $tabId): void
    {
        DB::transaction(function () use ($token, $tabId): void {
            $currentToken = PersonalAccessToken::query()
                ->lockForUpdate()
                ->findOrFail($token->getKey());

            $currentToken->forceFill([
                'active_tab_id' => $tabId,
                'active_tab_last_seen_at' => now(),
            ])->save();
        });
    }

    public function ownsTab(PersonalAccessToken $token, string $tabId): bool
    {
        return is_string($token->active_tab_id)
            && hash_equals($token->active_tab_id, $tabId);
    }

    public function isValidTabId(mixed $tabId): bool
    {
        return is_string($tabId)
            && preg_match(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
                $tabId
            ) === 1;
    }

    public function belongsToUser(PersonalAccessToken $token, mixed $user): bool
    {
        return $user !== null
            && $token->tokenable_type === $user->getMorphClass()
            && (string) $token->tokenable_id === (string) $user->getKey();
    }
}
