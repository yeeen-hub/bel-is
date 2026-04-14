<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TourismContentController;
use App\Http\Controllers\VirtualTourController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PublicRegController;
use App\Http\Controllers\FeeCategoryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\VisitorVisit;

// ── Route Model Binding ───────────────────────────────────────────────────────
// {visitor} in any route = VisitorVisit (UUID primary key).
Route::bind('visitor', fn($value) => VisitorVisit::findOrFail($value));

// ═════════════════════════════════════════════════════════════════════════════
// PUBLIC ROUTES — no auth required
// ═════════════════════════════════════════════════════════════════════════════

// ── Landing Page ──────────────────────────────────────────────────────────────
Route::get('/', function () {
    return Inertia::render('LandingPage', [
        'canLogin'       => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
})->name('home');

// ── Public Pre-Registration (Phase 2) ────────────────────────────────────────
// Privacy-Shielded: never auto-fills, never shows returning visitor data.
// Creates VisitorVisit (source='pre_registration') — hidden from all records
// until staff confirms the reference code at the checkpoint.
Route::get('/pre-register',        [PublicRegController::class, 'create'])->name('pre-register');
Route::post('/pre-register',       [PublicRegController::class, 'store'])->name('pre-register.store');
Route::post('/pre-register/group', [PublicRegController::class, 'storeGroup'])->name('pre-register.group');
Route::get('/pre-register/lookup', [PublicRegController::class, 'lookup'])->name('pre-register.lookup');

// ═════════════════════════════════════════════════════════════════════════════
// AUTHENTICATED ROUTES
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth'])->group(function () {

    // ── Dashboard ─────────────────────────────────────────────────────────────
    Route::get('/admindb', [DashboardController::class, 'index'])
        ->name('admindb');

    // ── Registration (Admin & Staff only) ─────────────────────────────────────
    Route::middleware(['role:admin|staff'])->group(function () {

        // Step 1 — Visitor registration form
        Route::get('/registration',       [VisitorController::class, 'create'])->name('registration');
        Route::post('/registration',      [VisitorController::class, 'store'])->name('registration.store');
        Route::post('/registration/group',[VisitorController::class, 'storeGroup'])->name('registration.group');

        // Returning visitor profile search (JSON — consumed by axios in AdminRegPage.vue)
        Route::get('/visitors/search-profile', [VisitorController::class, 'searchProfile'])
            ->name('visitors.search-profile');

        // Step 2 — Payment
        Route::get('/adminpay/{visitor}',  [ReceiptController::class, 'showPayment'])->name('adminpay');
        Route::post('/adminpay/{visitor}', [ReceiptController::class, 'store'])->name('adminpay.store');

        // Step 3 — Receipt
        Route::get('/adminreceipt/{visitor}', [ReceiptController::class, 'showReceipt'])->name('adminreceipt');
    });

    // ── Visitor Records ───────────────────────────────────────────────────────
    Route::middleware(['role:admin|staff|coordinator|lgu_official'])->group(function () {
        Route::get('/visitor-records',          [VisitorController::class, 'index'])->name('visitor-records');
        Route::get('/visitor-records/{visitor}',[VisitorController::class, 'show'])->name('visitor-records.show');
    });

    // ── Reports ───────────────────────────────────────────────────────────────
    Route::middleware(['role:admin|staff|coordinator|lgu_official'])->group(function () {
        Route::get('/reports', fn() => redirect()->route('reports.analytics'))->name('reports');
        Route::get('/reports/analytics',   [ReportController::class, 'analytics'])->name('reports.analytics');
        Route::get('/reports/temporal',    [ReportController::class, 'temporal'])->name('reports.temporal');
        Route::get('/reports/fee-revenue', [ReportController::class, 'feeRevenue'])->name('reports.fee-revenue');
        Route::get('/reports/behavioral',  [ReportController::class, 'behavioral'])->name('reports.behavioral');
    });

    // ── Fee revenue
    Route::get('/feerevenue', function () {
    return Inertia::render('AdminRepFeePage');
    })->name('feerevenue');

    // ── Demographics
    Route::get('/demographics', function () {
        return Inertia::render('AdminRepDemoPage');
    })->name('demographics');

    Route::get('/systemsettings', [FeeCategoryController::class, 'index'])
        ->name('systemsettings');

    Route::post('/admin/settings/fee-categories', [FeeCategoryController::class, 'update'])
        ->name('fee-categories.update');

    Route::get('/usermanagement', function () {
        return Inertia::render('AdminSetUMPage');
    })->name('usermanagement');
    
    Route::get('/auditlogs', function () {
        return Inertia::render('AdminSetALPage');
    })->name('auditlogs');

    Route::get('/websitecontent', function () {
        return Inertia::render('AdminSetWCPage');
    })->name('websitecontent');

    Route::get('/virtualtour', function () {
        return Inertia::render('AdminSetVTPage');
    })->name('virtualtour');

    Route::get('/securitysettings', function () {
        return Inertia::render('AdminSetSecPage');
    })->name('securitysettings');

    // ── Settings ──────────────────────────────────────────────────────────────
    Route::get('/settings',          [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/password',[SettingsController::class, 'updatePassword'])->name('settings.password');

    // ── User Management (Admin only) ──────────────────────────────────────────
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users',                        [UserController::class, 'index'])->name('users.index');
        Route::post('/users',                       [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}',                 [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle_active');
    });

    // ── Content Management (Admin & Coordinator) ──────────────────────────────
    Route::middleware(['role:admin|coordinator'])->group(function () {
        Route::get('/content',                      [TourismContentController::class, 'index'])->name('content.index');
        Route::post('/content',                     [TourismContentController::class, 'store'])->name('content.store');
        Route::put('/content/{tourismContent}',     [TourismContentController::class, 'update'])->name('content.update');
        Route::delete('/content/{tourismContent}',  [TourismContentController::class, 'destroy'])->name('content.destroy');
    });

    // ── Virtual Tour (Admin & Coordinator) ───────────────────────────────────
    Route::middleware(['role:admin|coordinator'])->group(function () {
        Route::get('/virtual-tour',                     [VirtualTourController::class, 'index'])->name('virtual_tour.index');
        Route::post('/virtual-tour',                    [VirtualTourController::class, 'store'])->name('virtual_tour.store');
        Route::put('/virtual-tour/{virtualHotspot}',    [VirtualTourController::class, 'update'])->name('virtual_tour.update');
        Route::delete('/virtual-tour/{virtualHotspot}', [VirtualTourController::class, 'destroy'])->name('virtual_tour.destroy');
    });

    // ── Audit Logs (Admin only) ───────────────────────────────────────────────
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');
    });

    // ── Breeze Profile ────────────────────────────────────────────────────────
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';