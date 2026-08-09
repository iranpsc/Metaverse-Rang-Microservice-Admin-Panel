<?php

namespace App\Services\BulkMessage;

use App\Models\User;

class MessagePlaceholderService
{
    public function replace(string $template, User $user): string
    {
        return str_replace(
            ['|name|', '|email|', '|code|'],
            [$user->name ?? '', $user->email ?? '', $user->code ?? ''],
            $template
        );
    }
}
