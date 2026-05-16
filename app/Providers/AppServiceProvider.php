<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\WhatsApp\HttpWhatsAppProvider;
use App\Support\WhatsApp\WhatsAppProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WhatsAppProvider::class, HttpWhatsAppProvider::class);
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        $this->configureRateLimiters();
    }

    private function configureRateLimiters(): void
    {
        // Login: 5 attempts per minute per IP+email combo (mitigates account enumeration + brute force).
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');
            $key = strtolower(trim($email)).'|'.$request->ip();

            return [
                Limit::perMinute(5)->by('login:'.Str::transliterate($key)),
            ];
        });

        // Forgot password: 3 attempts per 15 minutes per IP (avoid spamming reset emails).
        RateLimiter::for('password.email', function (Request $request) {
            return [
                Limit::perMinutes(15, 3)->by('password.email:'.$request->ip()),
            ];
        });

        // Filament admin login (super admin): tighter throttle.
        RateLimiter::for('filament-login', function (Request $request) {
            $email = (string) $request->input('email');
            $key = strtolower(trim($email)).'|'.$request->ip();

            return [
                Limit::perMinute(5)->by('filament-login:'.Str::transliterate($key)),
            ];
        });

        // Member invitations: 20 per hour per active organization.
        RateLimiter::for('organization-invitations', function (Request $request) {
            $orgId = $request->session()->get('active_organization_id') ?? 'guest';

            return [
                Limit::perHour(20)->by('org-invite:'.$orgId),
            ];
        });

        // WhatsApp alert dispatch: 100 per hour per active organization.
        RateLimiter::for('whatsapp-dispatch', function (Request $request) {
            $orgId = $request->session()->get('active_organization_id') ?? 'guest';

            return [
                Limit::perHour(100)->by('whatsapp:'.$orgId),
            ];
        });

        // Public certificate verification: prevent token guessing brute force.
        RateLimiter::for('certificate-verify', function (Request $request) {
            return [
                Limit::perMinute(20)->by('cert-verify:'.$request->ip()),
            ];
        });
    }
}
