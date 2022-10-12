<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    private \Illuminate\Support\LazyCollection $users;

    public function __construct()
    {
        $this->users = User::with('kyc')->lazy();
    }

    public function allUsers(): int
    {
        return $this->users->count();
    }

    public function verifiedEmailUsers(): int
    {
        return $this->users->reject(function ($user) {
            return ! $user->email_verified_at;
        })->count();
    }

    public function verifiedPhoneUsers(): int
    {
        return $this->users->reject(function ($user) {
            return ! $user->phone;
        })->count();
    }

    public function verifiedKycUsers(): int
    {
        return $this->users->reject(function ($user) {
            return ! $user->kyc || $user->kyc->status == 0;
        })->count();
    }
}
