<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    // ── Show login form ───────────────────────────────────────────────────────
    // Redirects to dashboard if already authenticated (from AuthController)
    public function create(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admindb');
        }

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status'           => session('status'),
        ]);
    }

    // ── Handle login ──────────────────────────────────────────────────────────
    // Merges both controllers:
    //   - LoginRequest handles throttling + basic validation (Breeze)
    //   - is_active check from AuthController
    //   - Custom error messages from AuthController
    //   - last_login_at update from both
    //   - remember me support from AuthController
    public function store(LoginRequest $request): RedirectResponse
    {
        // Check if user exists and account is active BEFORE attempting auth.
        // This gives a clear message instead of the generic "credentials" error.
        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->is_active) {
            return back()->withErrors([
                'email' => 'Your account is inactive or does not exist.',
            ])->onlyInput('email');
        }

        // Attempt authentication via LoginRequest (handles throttling).
        // Passes remember from the request for persistent sessions.
        $request->authenticate();

        $request->session()->regenerate();

        // Update last login timestamp
        $user->update(['last_login_at' => now()]);

        // Redirect to dashboard — not the Breeze default /dashboard
        return redirect()->intended(route('admindb'));
    }

    // ── Handle logout ─────────────────────────────────────────────────────────
    // Redirects to home (landing page) — matching your route setup
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}