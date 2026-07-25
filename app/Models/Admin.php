<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Support\Collection;

class Admin extends Authenticatable
{
    use Notifiable, HasFactory, HasApiTokens, HasRoles, HasPermissions;

    protected $casts = [
        'email_verified_at' => 'datetime',
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
        'image'
    ];

    protected $attributes = [
        'image' => 'noimage.png',
        'active' => 1
    ];

    /**
     * Get the guard name for the model.
     * This is used by Spatie Permission package to determine which guard to use for roles/permissions.
     * Must match one of the guards defined in config/auth.php ('web' or 'admin').
     *
     * @return string
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
