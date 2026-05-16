<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateGoogleOAuthUserAction;
use App\Actions\Auth\BuildGoogleOAuthRedirectUrlAction;
use App\Actions\Auth\ExchangeGoogleOAuthCodeAction;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class GoogleOAuthController extends Controller
{
    private const STATE_SESSION_KEY = 'auth.google_oauth_state';

    public function redirect(Request $request, BuildGoogleOAuthRedirectUrlAction $buildRedirectUrl): RedirectResponse
    {
        try {
            $redirectUrl = $buildRedirectUrl->execute(
                config('services.google'),
                static fn (string $state): mixed => $request->session()->put(self::STATE_SESSION_KEY, $state),
            );
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('login')
                ->with('error', $exception->getMessage());
        }

        return redirect()->away($redirectUrl);
    }

    public function callback(
        Request $request,
        ExchangeGoogleOAuthCodeAction $exchangeCode,
        AuthenticateGoogleOAuthUserAction $authenticateUser,
    ): RedirectResponse {
        $expectedState = $request->session()->pull(self::STATE_SESSION_KEY);

        if (! is_string($expectedState) || ! hash_equals($expectedState, (string) $request->query('state'))) {
            throw ValidationException::withMessages([
                'google' => 'Sesi login Google tidak valid. Silakan coba lagi.',
            ]);
        }

        if ($request->filled('error')) {
            throw ValidationException::withMessages([
                'google' => 'Login Google dibatalkan atau ditolak.',
            ]);
        }

        $code = $request->query('code');

        if (! is_string($code) || blank($code)) {
            throw ValidationException::withMessages([
                'google' => 'Kode otorisasi Google tidak ditemukan.',
            ]);
        }

        $googleUser = $exchangeCode->execute($code, config('services.google'));
        $authentication = $authenticateUser->execute($googleUser);

        if ($authentication->wasCreated) {
            event(new Registered($authentication->user));
        }

        Auth::login($authentication->user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
