<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Validator::extend(
            'is_valid_verify_code',
            'App\Rules\IsValidVerifyCode@passes',
            'کد تایید صحیح نیست!'
        );

        Validator::extend(
            'is_valid_access_password',
            'App\Rules\IsValidAccessPassword@passes',
            'رمز دسترسی صحیح نیست!'
        );
    }
}
