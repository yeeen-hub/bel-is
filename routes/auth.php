<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Support\Facades\Route;

// ═════════════════════════════════════════════════════════════════════════════
// GUEST ROUTES — only accessible when NOT logged in
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware('guest')->group(function () {

    // ── Login ─────────────────────────────────────────────────────────────────
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.post');

    // ── Password Reset ────────────────────────────────────────────────────────
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// ═════════════════════════════════════════════════════════════════════════════
// AUTHENTICATED ROUTES — only accessible when logged in
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware('auth')->group(function () {

    // ── Logout ────────────────────────────────────────────────────────────────
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // ── Password Confirmation ─────────────────────────────────────────────────
    // Shown when a sensitive action requires re-confirmation of password
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // ── Password Change ───────────────────────────────────────────────────────
    // Used by the Settings page — PUT /password
    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');
});