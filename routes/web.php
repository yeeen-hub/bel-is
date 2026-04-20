<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TourismContentController;
use App\Http\Controllers\VirtualTourController;
use App\Http\Controllers\FeeCategoryController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PublicRegController;
use App\Models\VisitorVisit;
use App\Http\Controllers\WebsiteContentController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::bind('visitor', fn($value) => VisitorVisit::findOrFail($value));

// PUBLIC ROUTES
Route::get('/pre-register',        [PublicRegController::class, 'create'])->name('pre-register');
Route::post('/pre-register',       [PublicRegController::class, 'store'])->name('pre-register.store');
Route::post('/pre-register/group', [PublicRegController::class, 'storeGroup'])->name('pre-register.group');
Route::get('/pre-register/lookup', [PublicRegController::class, 'lookup'])->name('pre-register.lookup');

// AUTHENTICATED ROUTES
Route::middleware(['auth'])->group(function () {

    // FIXED: Changed 'view analytics' to 'view_dashboard'
    Route::get('/admindb', [DashboardController::class, 'index'])
        ->middleware('permission:view_dashboard')
        ->name('admindb');

    // FIXED: Changed 'view visitors' to 'view_visitor_records'
    Route::middleware(['permission:view_visitor_records'])->group(function () {
        Route::get('/registration',       [VisitorController::class, 'create'])->name('registration');
        Route::post('/registration',      [VisitorController::class, 'store'])->name('registration.store');
        Route::post('/registration/group', [VisitorController::class, 'storeGroup'])->name('registration.group');
        Route::get('/visitors/search-profile', [VisitorController::class, 'searchProfile'])->name('visitors.search-profile');

        Route::get('/adminpay/{visitor}',  [ReceiptController::class, 'showPayment'])->name('adminpay');
        Route::post('/adminpay/{visitor}', [ReceiptController::class, 'store'])->name('adminpay.store');
        Route::get('/adminreceipt/{visitor}', [ReceiptController::class, 'showReceipt'])->name('adminreceipt');
        
        Route::get('/visitor-records',          [VisitorController::class, 'index'])->name('visitor-records');
        Route::get('/visitor-records/{visitor}', [VisitorController::class, 'show'])->name('visitor-records.show');
    });

    // FIXED: Changed 'view reports' to 'view_reports'
    Route::middleware(['permission:view_reports'])->group(function () {
        Route::get('/reports', fn() => redirect()->route('reports.analytics'))->name('reports');
        Route::get('/reports/analytics',   [ReportController::class, 'analytics'])->name('reports.analytics');
        Route::get('/reports/temporal',    [ReportController::class, 'temporal'])->name('reports.temporal');
        Route::get('/reports/fee-revenue', [ReportController::class, 'feeRevenue'])->name('reports.fee-revenue');
        Route::get('/reports/behavioral',  [ReportController::class, 'behavioral'])->name('reports.behavioral');
        
        Route::get('/feerevenue', fn() => Inertia::render('AdminRepFeePage'))->name('feerevenue');
        Route::get('/demographics', fn() => Inertia::render('AdminRepDemoPage'))->name('demographics');
    });

    // FIXED: Changed 'view users' to 'view_user_management'
    Route::middleware(['permission:view_user_management'])->group(function () {
        Route::get('/usermanagement', [UserController::class, 'index'])->name('usermanagement');
        Route::post('/usermanagement', [UserController::class, 'store'])->name('usermanagement.store');
        Route::patch('/usermanagement/{user}', [UserController::class, 'update'])->name('usermanagement.update');
        Route::patch('/usermanagement/{user}/toggle', [UserController::class, 'toggleActive'])->name('usermanagement.toggle');
        Route::post('/usermanagement/bulk-destroy', [UserController::class, 'bulkDestroy'])->name('usermanagement.bulk-destroy');
        Route::delete('/usermanagement/{user}/session', [UserController::class, 'forceSessionClear'])->name('usermanagement.clear_session');
    });

    // FIXED: Changed 'manage settings|manage system' to 'view_security|view_system_settings'
    Route::middleware(['permission:view_security|view_system_settings'])->group(function () {
        Route::get('/securitysettings', [SecurityController::class, 'index'])->name('securitysettings');
        Route::post('/security/rbac/update', [SecurityController::class, 'updateRBAC'])->name('security.rbac.update');
        Route::post('/security/password', [SecurityController::class, 'updatePassword'])->name('security.password.update');
        Route::post('/security/settings', [SecurityController::class, 'updateSecuritySettings'])->name('security.settings.update');
        Route::post('/security/sessions/logout-others', [SecurityController::class, 'logoutOthers'])->name('security.sessions.logout_others');
        
        Route::get('/auditlogs', function () { return Inertia::render('AdminSetALPage'); })->name('auditlogs');
        Route::get('/websitecontent', [WebsiteContentController::class, 'index'])->name('websitecontent');
        Route::get('/virtualtour', function () { return Inertia::render('AdminSetVTPage'); })->name('virtualtour');

        Route::get('/systemsettings', [FeeCategoryController::class, 'index'])->name('systemsettings');
        Route::post('/admin/settings/fee-categories', [FeeCategoryController::class, 'update'])->name('fee-categories.update');
    });

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Virtual Tour ────────────────────────────────────────
Route::get('/VTHome', function () {
    return inertia('YourMainPageName'); // The page where your Virtual Tour lives
});

// If you have many virtual tour routes, you might need a catch-all
Route::get('/location/{id}', function () {
    return inertia('YourMainPageName');
});

// ── Web Content (Hero/Home) ────────────────────────────────────────
Route::get('/', [WebsiteContentController::class, 'landingPage'])->name('home');
 
// Admin: Save hero section
Route::post('/admin/settings/website-content/hero', [WebsiteContentController::class, 'updateHero'])
    ->name('websitecontent.hero.update')
    ->middleware(['auth']);

// Admin: Save contact info section
Route::post('/admin/settings/website-content/contact', [WebsiteContentController::class, 'updateContact'])
    ->name('websitecontent.contact.update')
    ->middleware(['auth']);

// Admin: Attractions CRUD
Route::post('/admin/settings/website-content/attractions', [WebsiteContentController::class, 'storeAttraction'])
    ->name('websitecontent.attractions.store')
    ->middleware(['auth']);

Route::post('/admin/settings/website-content/attractions/{id}', [WebsiteContentController::class, 'updateAttraction'])
    ->name('websitecontent.attractions.update')
    ->middleware(['auth']);

Route::delete('/admin/settings/website-content/attractions/{id}', [WebsiteContentController::class, 'destroyAttraction'])
    ->name('websitecontent.attractions.destroy')
    ->middleware(['auth']);

require __DIR__.'/auth.php';