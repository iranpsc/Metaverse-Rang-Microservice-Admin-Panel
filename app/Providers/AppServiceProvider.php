<?php

namespace App\Providers;

use App\Observers\ModelActivityObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        Event::listen(
            Registered::class,
            SendEmailVerificationNotification::class,
        );

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
            $class = 'App\\Models\\'.basename($file, '.php');

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
