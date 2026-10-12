<?php

use App\Http\Controllers\Api\PartnerLifecycleController;
use App\Http\Controllers\Api\PartnerLookupController;
use App\Http\Controllers\Api\PartnerProvisionController;
use App\Http\Middleware\VerifyPortalProvisionSecret;
use Illuminate\Support\Facades\Route;

Route::middleware(VerifyPortalProvisionSecret::class)->prefix('internal')->group(function () {
    Route::post('partners/provision', [PartnerProvisionController::class, 'store']);
    Route::get('partners/{slug}', [PartnerLookupController::class, 'show']);
    Route::post('partners/{slug}/activate', [PartnerLifecycleController::class, 'activate']);
    Route::post('partners/{slug}/deactivate', [PartnerLifecycleController::class, 'deactivate']);
    Route::delete('partners/{slug}', [PartnerLifecycleController::class, 'destroy']);
});
