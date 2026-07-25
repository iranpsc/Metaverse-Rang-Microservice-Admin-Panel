<?php

namespace App\Services\BulkMessage;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

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
