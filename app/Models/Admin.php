<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, HasPermissions, HasRoles, Notifiable;

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'active',
        'image',
    ];

    protected $attributes = [
        'image' => 'noimage.png',
        'active' => 1,
    ];

    /**
     * Get the guard name for the model.
     * This is used by Spatie Permission package to determine which guard to use for roles/permissions.
     * Must match one of the guards defined in config/auth.php ('web' or 'admin').
     */
    public function getGuardName(): string
    {
        return 'admin';
    }

    public function getRoleTitles(): Collection
    {
        $this->loadMissing('roles');

        return $this->roles->pluck('title');
    }

    public function routeNotificationForKavenegar($driver, $notification = null)
    {
        return $this->phone;
    }
}
