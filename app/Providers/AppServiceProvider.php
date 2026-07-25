<?php

namespace App\Providers;

use App\Observers\ModelActivityObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

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

        $this->registerModelActivityObservers();
    }

    protected function registerModelActivityObservers(): void
    {
        $excluded = [
            Activity::class,
        ];

        foreach (glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\' . basename($file, '.php');

            if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
                continue;
            }

            if (in_array($class, $excluded, true)) {
                continue;
            }

            $class::observe(ModelActivityObserver::class);
        }
    }
}
