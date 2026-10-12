<?php

use App\Http\Controllers\ApiCredentialsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyContextController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\TransactionController;
use App\Http\Middleware\EnsureCompanyContext;
use App\Http\Middleware\EnsurePasswordChanged;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('login/two-factor', [AuthController::class, 'showTwoFactor'])->name('login.two-factor');
    Route::post('login/two-factor', [AuthController::class, 'verifyTwoFactor'])->name('login.two-factor.verify');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('password/change', [PasswordController::class, 'showChange'])->name('password.change');
    Route::put('password/change', [PasswordController::class, 'update'])->name('password.change.update');
});

Route::middleware(['auth', EnsurePasswordChanged::class, EnsureCompanyContext::class])->group(function () {
    Route::post('company/switch', [CompanyContextController::class, 'switch'])->name('company.switch');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('transactions/payins', [TransactionController::class, 'payins'])->name('transactions.payins');
    Route::get('transactions/payins/export', [TransactionController::class, 'exportPayins'])->name('transactions.payins.export');
    Route::get('transactions/payouts', [TransactionController::class, 'payouts'])->name('transactions.payouts');
    Route::get('transactions/payouts/export', [TransactionController::class, 'exportPayouts'])->name('transactions.payouts.export');

    Route::get('api-credentials', [ApiCredentialsController::class, 'show'])->name('api.credentials');
    Route::put('api-credentials', [ApiCredentialsController::class, 'update'])->name('api.credentials.update');

    Route::get('documentation', [DocumentationController::class, 'index'])->name('docs');
});
