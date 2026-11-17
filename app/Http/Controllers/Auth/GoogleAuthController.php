<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google login.
     */
    public function redirect()
    {
        
       return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google's authentication callback.
     */
    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $email = $googleUser->getEmail();

        if (!$email) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Google did not provide an email address.',
                ]);
        }

        // Only allow existing BRO360 accounts.
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Your Google account is not registered in BRO360.',
                ]);
        }

        Auth::login($user, true);

        request()->session()->regenerate();

        return redirect()->intended('/dashboard');
    }
}