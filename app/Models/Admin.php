<?php

    /**
     * Created by Reliese Model.
     */

    namespace App\Models;

use App\Models\Challenge\Question;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;

    class Admin extends Authenticatable
    {
        use Notifiable,HasFactory;


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
            'remember_token',
            'phone',
            'active',
            'access_password'
        ];

        /**
         * @return HasMany
         */
        public function questions(): HasMany
        {
            return $this->hasMany(Question::class);
        }

    }
