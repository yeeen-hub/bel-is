<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    // ── Authenticate with all three security layers ───────────────────────────
    public function authenticate(): void
    {
        // Layer 1: Rate limiting — 5 attempts per email+IP, 60s lockout
        $this->ensureIsNotRateLimited();

        // Layer 2: Inactive account check — before attempting credentials
        // Prevents timing-based enumeration of active vs inactive accounts
        $user = User::where('email', $this->string('email'))->first();
        if ($user && !$user->is_active) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Please contact the system administrator.',
            ]);
        }

        // Layer 3: Standard credential check
        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    // ── Rate limiting: 5 attempts, then 60-second lockout ────────────────────
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }
        
        // GET FROM DATABASE INSTEAD OF HARDCODED '5'
        $settings = DB::table('security_settings')->where('id', 1)->first();
        $maxAttempts = $settings->max_login_attempts ?? 5;
        $lockoutMins = $settings->lockout_duration ?? 15;

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    // ── Throttle key: email + IP ──────────────────────────────────────────────
    // Combining both prevents distributed brute-force across IPs
    // and prevents one bad actor from locking out a legitimate user by email alone
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}