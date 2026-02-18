<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        VerifyEmail::createUrlUsing(function (object $notifiable) {
            // Link ini akan mengarah ke React (misal: localhost:3000/verify-email)
            $frontendUrl = config('app.frontend_url') . '/verify-email';

            // Generate temporary signed URL untuk keamanan
            $verifyUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60), // Link berlaku 60 menit
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            return $frontendUrl . '?url=' . urlencode($verifyUrl);
        });
    }
}
