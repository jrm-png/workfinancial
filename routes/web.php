<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\WorkPlanController;
use App\Http\Controllers\FinancialPlanController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\Admin\DropdownSettingController;
use Spatie\Browsershot\Browsershot;
use App\Http\Controllers\MassReviewController;

Route::get('/', function () {
    return view('auth.login');
});

// --- AUTHENTICATED ROUTES ---
Route::middleware(['auth'])->group(function () {
    Route::delete('/workplan/{id}', [FormController::class, 'destroy']);

    // Dashboard (Main Hub)
    Route::get('/dashboard', [FormController::class, 'dashboard'])->name('dashboard');
    

    Route::get('/dashboardfinance', [FormController::class, 'financeDashboard'])->name('dashfinance');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // UNIFIED PLANS (FormController)
    // These handle the creation and submission of the integrated Work & Financial Plan
    Route::get('/plans/create', [FormController::class, 'create'])->name('plans.create');
    Route::post('/plans', [FormController::class, 'store'])->name('plans.store');
    
    // LIST VIEWS (AG Grid Backends)
    // Dapat nakaturo sa 'index' para ma-trigger yung fetching ng $settings
    Route::get('/workplan/list', [FormController::class, 'index'])->name('workplan.list');
        Route::get('/financialplan/list', [FinancialPlanController::class, 'list'])->name('financialplan.list');
        Route::get('/workplan/unified/{id}', [WorkPlanController::class, 'getUnifiedDetails']);

    Route::post('/workplan/update-status/{formId}', [FormController::class, 'updateStatus']);

        // LEGACY/INDIVIDUAL ACTIONS (Optional - keep if still needed)
        Route::put('/workplan/{workplan}', [WorkPlanController::class, 'update'])->name('workplan.update');
        Route::delete('/workplan/{workplan}', [WorkPlanController::class, 'destroy'])->name('workplan.destroy');

    Route::get('/workplan/view-attachment', [FormController::class, 'viewAttachmentWFP'])->name('workplan.view-attachment');
            
        Route::get('/export-center', [FormController::class, 'exportView'])->name('plans.export.view');
        Route::get('/export-center/generate', [FormController::class, 'generatePdf'])->name('plans.export.generate');

    Route::get('/plans/{id}/edit', [FormController::class, 'edit'])->name('plans.edit');
    Route::put('/plans/{id}/save', [FormController::class, 'save'])->name('plans.save');
    Route::put('/plans/{id}/update', [FormController::class, 'update'])->name('plans.update');

    Route::get('/plans/drafts', [FormController::class, 'drafts'])->name('plans.drafts');

    Route::get('/financialplan/list', [FinancialPlanController::class, 'index'])->name('financial.list');
    Route::delete('/financialplan/{workplan}', [FinancialPlanController::class, 'destroy'])->name('financial.destroy');


    Route::get('/division/{r_center}', [FormController::class, 'divisionProfile'])->name('division.profile');
    
    Route::get('/plans/copy', [FormController::class, 'copySearch'])->name('plans.copy.search');
    Route::get('/plans/copy/{id}', [FormController::class, 'copyLoad'])->name('plans.copy.load');
    Route::post('/plans/copy/batch', [FormController::class, 'batchCopy'])->name('plans.copy.batch');
});

Route::get('/change-password', [PasswordChangeController::class, 'showForm'])->name('password.change.form');
Route::post('/change-password', [PasswordChangeController::class, 'update'])->name('password.change.update');

// --- ADMIN ONLY ROUTES ---
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    
    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::post('/users/{user}/reset-password', [UserController::class, 'reset'])->name('admin.users.reset');

    // edit delete bai
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    
    // SETTINGS & CONTROL PANEL
    Route::get('/settings', [FormController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [FormController::class, 'updateSettings'])->name('settings.update');

    Route::prefix('admin')->name('admin.')->group(function () {
    // Main View page for dropdown management
    Route::get('/dropdowns', [App\Http\Controllers\Admin\DropdownSettingsController::class, 'index'])->name('dropdowns.index');
    
    // Actions for adding and deleting options
    Route::post('/dropdowns', [App\Http\Controllers\Admin\DropdownSettingsController::class, 'store'])->name('dropdowns.store');
    Route::delete('/dropdowns/{id}', [App\Http\Controllers\Admin\DropdownSettingsController::class, 'destroy'])->name('dropdowns.destroy');
});

    // AJAX Route for the Override Toggle (impressive UI/UX)
    Route::post('/users/{user}/toggle-override', [FormController::class, 'toggleOverride'])->name('admin.users.toggle_override');
});

Route::middleware(['auth'])->group(function () {
    // Admin & Monitor Settings Panel Routes
    Route::get('/admin/settings', [FormController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings/dropdowns', [FormController::class, 'storeDropdownItem'])->name('admin.dropdowns.store');
    Route::delete('/admin/settings/dropdowns/{id}', [FormController::class, 'deleteDropdownItem'])->name('admin.dropdowns.delete');
    
    // Core Forms Processing Paths
    Route::get('/plans/create', [FormController::class, 'create'])->name('plans.create');
});

Route::middleware(['auth'])->group(function () {
Route::get('/mass-review', [MassReviewController::class, 'index'])->name('mass-review.index');


Route::post('/mass-review/approve', [MassReviewController::class, 'approve'])->name('mass-review.approve');
Route::post('/mass-review/revise', [MassReviewController::class, 'revise'])->name('mass-review.revise');
Route::post('/mass-review/for-reviewal', [MassReviewController::class, 'forReviewal'])->name('mass-review.for-reviewal');
Route::post('/mass-review/submit-to-finance', [MassReviewController::class, 'submitToFinance'])->name('mass-review.submit-to-finance');


});

require __DIR__.'/auth.php';