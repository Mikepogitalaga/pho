<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Number of failed attempts before CAPTCHA is required.
     */
    private const CAPTCHA_THRESHOLD = 4;

    /**
     * Number of failed attempts before the account locks.
     */
    private const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * First lock duration in minutes.
     */
    private const FIRST_LOCK_MINUTES = 5;

    /**
     * Subsequent lock duration in minutes.
     */
    private const EXTENDED_LOCK_MINUTES = 15;

    /**
     * Maximum failed login attempts per IP within the window before rejecting.
     */
    private const IP_MAX_ATTEMPTS = 50;

    /**
     * IP rate-limit window in minutes.
     */
    private const IP_RATE_LIMIT_MINUTES = 15;

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.auth', ['isRegister' => false]);
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = $credentials['email'];
        $ip = $request->ip();
        $userAgent = $request->userAgent() ?? 'Unknown';

        // Check IP rate limit first
        if ($this->isIpRateLimited($ip)) {
            $this->logLoginAttempt($email, $ip, $userAgent, false, 'ip_rate_limited');
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Too many login attempts from this network. Please try again later.']);
        }

        $user = User::where('email', $email)->first();

        // Account lockout check
        if ($user && $user->isLocked()) {
            $this->logLoginAttempt($email, $ip, $userAgent, false, 'account_locked');
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Your account is locked due to too many failed login attempts. Please try again in '.$user->lockMinutesRemaining().' minute(s) or contact the administrator.']);
        }

        // CAPTCHA check
        $requiresCaptcha = $this->requiresCaptcha($ip);
        if ($requiresCaptcha) {
            $captchaInput = $request->input('captcha');
            $captchaSession = $request->session()->get('login_captcha');

            if (empty($captchaInput) || empty($captchaSession) || !hash_equals((string) $captchaSession, strtolower((string) $captchaInput))) {
                $this->logLoginAttempt($email, $ip, $userAgent, false, 'captcha_failed');
                $this->generateCaptcha($request);
                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors(['captcha' => 'Incorrect CAPTCHA. Please try again.']);
            }
        }

        // Apply progressive delay
        if ($user) {
            $delay = $user->getThrottleDelay();
            if ($delay > 0) {
                sleep($delay);
            }
        }

        // Credential validation
        $credentialsValid = false;
        if ($user && Hash::check($credentials['password'], $user->password)) {
            $credentialsValid = true;
        }

        if (! $credentialsValid) {
            // Unknown email — generic message so accounts cannot be enumerated.
            if (! $user) {
                $this->recordFailedAttempt($email, $ip, $userAgent);
                $this->logLoginAttempt($email, $ip, $userAgent, false, 'invalid_credentials');
                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors(['email' => 'Invalid username or password.']);
            }

            // Account deactivated by an administrator.
            if (! $user->is_active) {
                $this->recordFailedAttempt($email, $ip, $userAgent);
                $this->logLoginAttempt($email, $ip, $userAgent, false, 'account_deactivated');
                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors(['email' => 'Your account has been deactivated. Please contact the administrator.']);
            }

            // Failed password — increment attempts and apply lockout if needed.
            $user->incrementFailedLoginAttempts();
            $this->recordFailedAttempt($email, $ip, $userAgent);
            $this->logLoginAttempt($email, $ip, $userAgent, false, 'invalid_password');

            // Re-check lock after recording failure
            $user->refresh();
            if ($user->isLocked()) {
                $this->logLoginAttempt($email, $ip, $userAgent, false, 'account_locked');
                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors(['email' => 'Your account is locked due to too many failed login attempts. Please try again in '.$user->lockMinutesRemaining().' minute(s) or contact the administrator.']);
            }

            // Progressive delay info for user
            $remaining = max(0, self::MAX_LOGIN_ATTEMPTS - $user->failed_login_attempts);
            $message = 'Invalid username or password.';
            if ($remaining > 0) {
                $message .= " {$remaining} attempt(s) remaining before your account is locked.";
            }

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => $message]);
        }

        // Successful login
        $user->resetLoginAttempts();
        $this->logLoginAttempt($email, $ip, $userAgent, true);
        $this->clearIpRateLimit($ip);
        $request->session()->forget('login_captcha');

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Record a failed attempt in cache for IP rate limiting.
     */
    private function recordFailedAttempt(string $email, string $ip, string $userAgent): void
    {
        $key = "login_attempt:{$ip}";
        Cache::add($key, 1, now()->addMinutes(self::IP_RATE_LIMIT_MINUTES));
        Cache::increment($key);
    }

    /**
     * Check if the IP has exceeded the rate limit.
     */
    private function isIpRateLimited(string $ip): bool
    {
        $key = "login_attempt:{$ip}";
        $attempts = Cache::get($key, 0);

        return $attempts >= self::IP_MAX_ATTEMPTS;
    }

    /**
     * Clear the IP rate-limit counter.
     */
    private function clearIpRateLimit(string $ip): void
    {
        Cache::forget("login_attempt:{$ip}");
    }

    /**
     * Determine whether CAPTCHA is required for this IP.
     */
    private function requiresCaptcha(string $ip): bool
    {
        $key = "login_failed:{$ip}";
        $failures = Cache::get($key, 0);

        return $failures >= self::CAPTCHA_THRESHOLD;
    }

    /**
     * Generate a simple math CAPTCHA and store in session.
     */
    private function generateCaptcha(Request $request): void
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $answer = $a + $b;

        $request->session()->put('login_captcha', (string) $answer);
        $request->session()->flash('login_captcha_question', "What is {$a} + {$b}?");
    }

    /**
     * Log a login attempt to the database.
     */
    private function logLoginAttempt(string $email, string $ip, ?string $userAgent, bool $success, ?string $failureReason = null): void
    {
        try {
            LoginAttempt::create([
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'success' => $success,
                'failure_reason' => $failureReason,
            ]);

            if ($success) {
                // Increment IP success counter for CAPTCHA logic
                $key = "login_success:{$ip}";
                Cache::add($key, 1, now()->addHours(24));
                Cache::forget("login_failed:{$ip}");
            } else {
                // Increment IP failure counter for CAPTCHA logic
                $key = "login_failed:{$ip}";
                Cache::add($key, 1, now()->addHours(24));
                Cache::increment($key);
            }
        } catch (\Throwable) {
            // Never break login due to logging failure
        }
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
