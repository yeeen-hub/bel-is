<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     *
     * If the user is already authenticated but their stored session ID no
     * longer matches the current session (e.g. they closed a tab without
     * logging out and then came back), we clear the stale auth state so
     * they can log in fresh instead of being bounced to the dashboard.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Session ID matches → genuinely still logged in → send to dashboard
            if ($user->current_session_id === session()->getId()) {
                return redirect()->intended(route('admindb'));
            }

            // Session ID mismatch → stale auth cookie from a previously closed
            // tab. Log out silently so the login page renders normally.
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status'           => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * After successful authentication we immediately store the new session ID
     * on the user record. This is the "ownership token" that the
     * ValidateSessionOwnership middleware checks on every subsequent request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Rotate the session ID to prevent session fixation attacks
        $request->session()->regenerate();

        // Stamp the new session ID onto the user record so the middleware
        // can verify ownership on every subsequent authenticated request.
        $user = Auth::user();
        $user->current_session_id = session()->getId();
        $user->last_login_at      = now();
        $user->save();

        return redirect()->intended(route('admindb'));
    }

    /**
     * Destroy the authenticated session.
     *
     * Clear the stored session ID so the account is fully released and
     * another device / browser tab can log in.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Clear ownership token before logging out
        if (Auth::check()) {
            $user = Auth::user();
            $user->current_session_id = null;
            $user->save();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}