<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/**
 * "Sign in with Google". An editor or owner picks their Google (Gmail) account;
 * the app signs them in only if that email is already a registered TROV account,
 * so it stays an authorised-employee system rather than open sign-up.
 */
class GoogleAuthController extends Controller
{
    public function redirect()
    {
        // If Google sign-in hasn't been configured yet, don't bounce the user
        // to Google with an empty client_id (that shows an ugly "Access blocked"
        // page). Send them back with a clear message and let them use a password.
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in isn\'t set up yet. Please log in with your email and password.',
            ]);
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in was cancelled or failed. Please try again.',
            ]);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'No TROV account uses that Google address. Ask an owner to add you.',
            ]);
        }
        if (! $user->is_active) {
            return redirect()->route('login')->withErrors([
                'email' => 'This account is not active. Ask an owner to reactivate it.',
            ]);
        }

        // Remember which Google identity this account signed in with.
        if (! $user->google_id) {
            $user->forceFill(['google_id' => $googleUser->getId()])->save();
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->route('home');
    }
}
