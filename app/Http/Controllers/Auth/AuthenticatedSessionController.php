<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    // ── Show login form ───────────────────────────────────────────────────────
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status'           => session('status'),
        ]);
    }

    // ── Handle login ─────────────────────────────────────────────────────────
    // LoginRequest::authenticate() handles credentials + rate limiting (5 attempts).
    // We add SSE collision check on top of that.
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user      = $request->user();
        $sessionId = $request->session()->getId();

        // ── SSE: One account, one device ──────────────────────────────────────
        // Scenario A — NULL (Vacant): allow
        // Scenario B — Same session ID (same browser refresh): allow
        // Scenario C — Different session ID (another device): BLOCK
        if (
            $user->current_session_id !== null &&
            $user->current_session_id !== $sessionId
        ) {
            AuditLog::create([
                'user_id'     => $user->id,
                'action'      => 'session_blocked',
                'module'      => 'auth',
                'target_type' => 'User',
                'target_id'   => (string) $user->id,
                'new_values'  => json_encode([
                    'reason'  => 'SSE collision — account active on another device',
                    'blocked' => $sessionId,
                    'active'  => $user->current_session_id,
                ]),
                'ip_address'  => $request->ip(),
            ]);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Access Denied: This account is currently active on another device. Please log out from the other terminal to continue.',
            ]);
        }

        // ── Allow: anchor session to this user ────────────────────────────────
        $request->session()->regenerate();

        $user->update([
            'current_session_id' => $request->session()->getId(),
            'last_login_at'      => now(),
        ]);

        AuditLog::create([
            'user_id'     => $user->id,
            'action'      => 'login',
            'module'      => 'auth',
            'target_type' => 'User',
            'target_id'   => (string) $user->id,
            'new_values'  => json_encode(['ip' => $request->ip()]),
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->intended(route('admindb'));
    }

    // ── Handle logout — atomic release ────────────────────────────────────────
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            // Clear session BEFORE destroying the cookie — account is
            // immediately Vacant so the next shift can log in right away
            $user->update(['current_session_id' => null]);

            AuditLog::create([
                'user_id'     => $user->id,
                'action'      => 'logout',
                'module'      => 'auth',
                'target_type' => 'User',
                'target_id'   => (string) $user->id,
                'new_values'  => json_encode(['ip' => $request->ip()]),
                'ip_address'  => $request->ip(),
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}