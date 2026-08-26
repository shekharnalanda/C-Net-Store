<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\BusinessApprovalController;
use App\Http\Controllers\Api\Admin\ProductApprovalController;

Route::middleware(['auth:sanctum', 'role:super_admin,staff'])->group(function (): void {
    Route::get('/dashboard', fn () => response()->json(['status' => 'ready']));
    Route::get('/businesses', [BusinessApprovalController::class, 'index']);
    Route::patch('/businesses/{business}', [BusinessApprovalController::class, 'update']);
    Route::get('/products', [ProductApprovalController::class, 'index']);
    Route::patch('/products/{product}', [ProductApprovalController::class, 'update']);
});

