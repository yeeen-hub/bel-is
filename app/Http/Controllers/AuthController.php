<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Inertia\Inertia;

class AuthController extends Controller
{
    // Show login page
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return Inertia::render('Auth/Login');
    }

    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if user exists and is active
        if (!$user || !$user->is_active) {
            return back()->withErrors([
                'email' => 'Your account is inactive or does not exist.',
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->remember)) {
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ]);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        $request->session()->regenerate();

        return redirect()->route('admindb');
    }

    // Handle logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}