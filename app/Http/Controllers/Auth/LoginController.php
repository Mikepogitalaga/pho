<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Number of failed attempts allowed before the account locks.
     */
    private const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * How long (in minutes) an account stays locked after too many failed attempts.
     */
    private const LOCK_MINUTES = 15;

    public function showLoginForm()
    {
        return view('auth.auth', ['isRegister' => false]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // Unknown email — generic message so accounts cannot be enumerated.
        if (! $user) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'The provided credentials do not match our records.']);
        }

        // Account deactivated by an administrator.
        if (! $user->is_active) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Your account has been deactivated. Please contact the administrator.']);
        }

        // Currently locked because of too many failed attempts.
        if ($user->isLocked()) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Your account is locked due to too many failed login attempts. Please try again in '.$user->lockMinutesRemaining().' minute(s) or contact the administrator.']);
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            return $this->handleFailedAttempt($request, $user);
        }

        // Successful login — clear the failure counter and any expired lock.
        $user->resetLoginAttempts();

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Record a failed login attempt and lock the account once the limit is reached.
     */
    private function handleFailedAttempt(Request $request, User $user)
    {
        $attempts = $user->failed_login_attempts + 1;
        $remaining = max(0, self::MAX_LOGIN_ATTEMPTS - $attempts);

        $user->forceFill([
            'failed_login_attempts' => $attempts,
            'locked_until' => $attempts >= self::MAX_LOGIN_ATTEMPTS
                ? now()->addMinutes(self::LOCK_MINUTES)
                : null,
        ])->save();

        $message = $attempts >= self::MAX_LOGIN_ATTEMPTS
            ? 'Too many failed login attempts. Your account has been locked for '.self::LOCK_MINUTES.' minutes.'
            : 'Incorrect email or password. '.$remaining.' attempt(s) remaining before your account is locked.';

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => $message]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

