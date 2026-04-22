<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidateSessionOwnership
{
    /**
     * Handle an incoming request.
     *
     * For every authenticated request we compare the current PHP session ID
     * against the one stored in users.current_session_id.
     *
     * Mismatch scenarios this catches:
     *  - A second browser/device logs in, pushing a new session ID to the DB.
     *    The original session is now orphaned and gets terminated here.
     *  - An admin forces a user's session clear via User Management.
     *    current_session_id becomes NULL → every request from that user is
     *    terminated until they log in again.
     *
     * NOTE: expire_on_close=true in session.php already handles the
     * "tab closed without logout" case at the browser level. This middleware
     * is the server-side safety net for cross-device and force-logout cases.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user              = Auth::user();
            $currentSessionId  = session()->getId();

            // No stored session ID (cleared by admin or logout) OR
            // stored ID does not match this request's session → terminate
            if (
                empty($user->current_session_id) ||
                $user->current_session_id !== $currentSessionId
            ) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Inertia requests get a 409 so the frontend does a full
                // redirect rather than trying to render a partial response
                if ($request->header('X-Inertia')) {
                    return response()->json(['message' => 'Session expired.'], 409);
                }

                return redirect()->route('login')
                    ->with('status', 'Your session has expired. Please log in again.');
            }
        }

        return $next($request);
    }
}