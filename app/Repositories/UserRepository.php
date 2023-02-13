<?php

namespace App\Repositories;

use App\Models\Kyc;
use App\Models\User;

class UserRepository
{
    public function allUsers(): int
    {
        return User::count();
    }

    public function verifiedEmailUsers(): int
    {
        return User::whereNotNull('email_verified_at')->count();
    }

    public function verifiedPhoneUsers(): int
    {
        return User::whereNotNull('phone_verified_at')->count();
    }

    public function verifiedKycUsers(): int
    {
        return Kyc::whereStatus(1)->count();
    }
}
