<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Support\Collection;

class Admin extends Authenticatable
{
    use Notifiable, HasFactory, HasRoles, HasPermissions;

    protected $dates = [
        'email_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'active',
        'access_password',
        'image'
    ];

    protected $attributes = [
        'image' => 'noimage.png',
        'active' => 1
    ];

    public function getRoleTitles(): Collection
    {
        $this->loadMissing('roles');

        return $this->roles->pluck('title');
    }
}
