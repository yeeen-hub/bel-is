<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FeeCategoryController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PublicRegController;
use App\Http\Controllers\SitioController;
use App\Http\Controllers\BarangayAttractionController;
use App\Models\VisitorVisit;
use App\Models\UnrecognizedAttraction;
use App\Http\Controllers\WebsiteContentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuditLogController;

Route::bind('visitor', fn($value) => VisitorVisit::findOrFail($value));

// ── PUBLIC ROUTES ─────────────────────────────────────────────────────────────
Route::get('/pre-register',        [PublicRegController::class, 'create'])->name('pre-register');
Route::post('/pre-register',       [PublicRegController::class, 'store'])->name('pre-register.store');
Route::post('/pre-register/group', [PublicRegController::class, 'storeGroup'])->name('pre-register.group');
Route::get('/pre-register/lookup', [PublicRegController::class, 'lookup'])->name('pre-register.lookup');

// Public JSON endpoints for registration form dropdowns
Route::get('/api/barangay-attractions', [BarangayAttractionController::class, 'list'])->name('api.barangay-attractions');
Route::get('/api/sitios',               [SitioController::class, 'list'])->name('api.sitios');

// ── AUTHENTICATED ROUTES ──────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::get('/admindb', [DashboardController::class, 'index'])
        ->middleware('permission:view_dashboard')
        ->name('admindb');

    // ── Visitor registration & payment ────────────────────────────────────────
    Route::middleware(['permission:view_visitor_records'])->group(function () {
        Route::get('/registration',        [VisitorController::class, 'create'])->name('registration');
        Route::post('/registration',       [VisitorController::class, 'store'])->name('registration.store');
        Route::post('/registration/group', [VisitorController::class, 'storeGroup'])->name('registration.group');
        Route::get('/visitors/search-profile', [VisitorController::class, 'searchProfile'])->name('visitors.search-profile');

        Route::get('/adminpay/{visitor}',     [ReceiptController::class, 'showPayment'])->name('adminpay');
        Route::post('/adminpay/{visitor}',    [ReceiptController::class, 'store'])->name('adminpay.store');
        Route::get('/adminreceipt/{visitor}', [ReceiptController::class, 'showReceipt'])->name('adminreceipt');

        Route::get('/visitor-records',           [VisitorController::class, 'index'])->name('visitor-records');
        Route::get('/visitor-records/{visitor}', [VisitorController::class, 'show'])->name('visitor-records.show');

        // Mark a pending visit as No Show — requires edit_payment permission
        Route::post('/adminpay/{visitor}/no-show', [ReceiptController::class, 'markNoShow'])
            ->middleware('permission:edit_payment')
            ->name('adminpay.no-show');
    });

    // ── Reports ───────────────────────────────────────────────────────────────
    Route::middleware(['permission:view_reports'])->group(function () {
        Route::get('/reports',             fn() => redirect()->route('reports.analytics'))->name('reports');
        Route::get('/reports/analytics',   [ReportController::class, 'analytics'])->name('reports.analytics');
        Route::get('/reports/demographics',[ReportController::class, 'demographics'])->name('reports.demographics');
        Route::get('/reports/fee-revenue', [ReportController::class, 'feeRevenue'])->name('reports.fee-revenue');
        Route::get('/reports/temporal',    [ReportController::class, 'temporal'])->name('reports.temporal');
        Route::get('/reports/behavioral',  [ReportController::class, 'behavioral'])->name('reports.behavioral');

        // Old aliases — redirect to canonical routes
        Route::get('/feerevenue',   fn() => redirect()->route('reports.fee-revenue'))->name('feerevenue');
        Route::get('/demographics', fn() => redirect()->route('reports.demographics'))->name('demographics');
    });

    // ── User management ───────────────────────────────────────────────────────
    Route::middleware(['permission:view_user_management'])->group(function () {
        Route::get('/usermanagement',                   [UserController::class, 'index'])->name('usermanagement');
        Route::post('/usermanagement',                  [UserController::class, 'store'])->name('usermanagement.store');
        Route::patch('/usermanagement/{user}',          [UserController::class, 'update'])->name('usermanagement.update');
        Route::patch('/usermanagement/{user}/toggle',   [UserController::class, 'toggleActive'])->name('usermanagement.toggle');
        Route::post('/usermanagement/bulk-destroy',     [UserController::class, 'bulkDestroy'])->name('usermanagement.bulk-destroy');
        Route::delete('/usermanagement/{user}/session', [UserController::class, 'forceSessionClear'])->name('usermanagement.clear_session');
    });

    // ── System settings (security, fee categories, sitios, attractions) ───────
    Route::middleware(['permission:view_security|view_system_settings'])->group(function () {

        // Security
        Route::get('/securitysettings',                 [SecurityController::class, 'index'])->name('securitysettings');
        Route::post('/security/rbac/update',            [SecurityController::class, 'updateRBAC'])->name('security.rbac.update');
        Route::post('/security/password',               [SecurityController::class, 'updatePassword'])->name('security.password.update');
        Route::post('/security/settings',               [SecurityController::class, 'updateSecuritySettings'])->name('security.settings.update');
        Route::post('/security/sessions/logout-others', [SecurityController::class, 'logoutOthers'])->name('security.sessions.logout_others');

        // Other settings sub-pages
        Route::get('/websitecontent', [WebsiteContentController::class, 'index'])->name('websitecontent');
        Route::get('/auditlogs',      [AuditLogController::class, 'index'])->name('auditlogs');
        Route::get('/virtualtour',    fn() => Inertia::render('AdminSetVTPage'))->name('virtualtour');
        Route::get('/usermanagement', [UserController::class, 'index'])->name('usermanagement');

        // ── System Settings main page (fee categories + sitios + attractions) ─
        // FIX: Both /settings and /systemsettings point to FeeCategoryController@index
        // so the page always receives sitios, barangayAttractions, etc.
        // Previously /settings used SettingsController which didn't pass those props.
        Route::get('/systemsettings', [FeeCategoryController::class, 'index'])->name('systemsettings');

        // ── Fee categories ─────────────────────────────────────────────────────
        Route::post('/admin/settings/fee-categories', [FeeCategoryController::class, 'update'])->name('fee-categories.update');

        // ── Sitio management ───────────────────────────────────────────────────
        Route::post('/admin/settings/sitios', [FeeCategoryController::class, 'updateSitios'])->name('sitios.update');

        // ── Attraction management ──────────────────────────────────────────────
        Route::post('/admin/settings/attractions', [FeeCategoryController::class, 'updateAttractions'])->name('barangay-attractions.update-all');

        // ── Unrecognized destination reports ───────────────────────────────────
        Route::patch('/admin/settings/unrecognized/{id}/review',
            [FeeCategoryController::class, 'reviewUnrecognized'])->name('fee-categories.review-unrecognized');
        Route::post('/admin/settings/unrecognized/{id}/add-to-attractions',
            [FeeCategoryController::class, 'addFromUnrecognized'])->name('fee-categories.add-from-unrecognized');
    });

    // ── General settings (password change, profile preferences) ──────────────
    // FIX: Now uses FeeCategoryController@index so all props (sitios, attractions)
    // are available when the user navigates via the "General Settings" nav tab.
    Route::get('/settings',           [FeeCategoryController::class, 'index'])->name('settings');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Virtual Tour ──────────────────────────────────────────────────────────────
Route::get('/VTHome',        fn() => inertia('YourMainPageName'));
Route::get('/location/{id}', fn() => inertia('YourMainPageName'));

// ── Landing page & website content ───────────────────────────────────────────
Route::get('/', [WebsiteContentController::class, 'landingPage'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::post('/admin/settings/website-content/hero',    [WebsiteContentController::class, 'updateHero'])->name('websitecontent.hero.update');
    Route::post('/admin/settings/website-content/contact', [WebsiteContentController::class, 'updateContact'])->name('websitecontent.contact.update');

    Route::post('/admin/settings/website-content/attractions',        [WebsiteContentController::class, 'storeAttraction'])->name('websitecontent.attractions.store');
    Route::post('/admin/settings/website-content/attractions/{id}',   [WebsiteContentController::class, 'updateAttraction'])->name('websitecontent.attractions.update');
    Route::delete('/admin/settings/website-content/attractions/{id}', [WebsiteContentController::class, 'destroyAttraction'])->name('websitecontent.attractions.destroy');

    Route::post('/admin/settings/website-content/about',                     [WebsiteContentController::class, 'updateAbout'])->name('websitecontent.about.update');
    Route::post('/admin/settings/website-content/about/images',              [WebsiteContentController::class, 'storeAboutImage'])->name('websitecontent.about.images.store');
    Route::delete('/admin/settings/website-content/about/images/{id}',       [WebsiteContentController::class, 'destroyAboutImage'])->name('websitecontent.about.images.destroy');
});

require __DIR__.'/auth.php';