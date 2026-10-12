<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Story;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        Relation::morphMap([
            'user' => User::class,
            'post' => Post::class,
            'comment' => Comment::class,
            'story' => Story::class,
        ]);

        \Illuminate\Support\Facades\Gate::policy(\App\Models\Profile::class, \App\Policies\ProfilePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);

        Event::listen(Registered::class, SendEmailVerificationNotification::class);

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            return "{$frontendUrl}/reset-password?token={$token}&email=".urlencode($user->email);
        });

        RateLimiter::for('login', function (Request $request) {
            $identifier = (string) $request->input('login', $request->input('email', ''));
            $key = Str::transliterate(Str::lower($identifier).'|'.$request->ip());

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('forgot-password', function (Request $request) {
            $email = (string) $request->input('email', '');
            $key = Str::transliterate(Str::lower($email).'|'.$request->ip());

            return Limit::perMinute(5)->by($key);
        });
    }
}
