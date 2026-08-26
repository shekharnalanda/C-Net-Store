<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\BusinessApprovalController;
use App\Http\Controllers\Api\Admin\ProductApprovalController;
use App\Http\Controllers\Api\Admin\DeliveryAssignmentController;
use App\Http\Controllers\Api\Admin\SettlementController;
use App\Http\Controllers\Api\Admin\ContentController;

Route::middleware(['auth:sanctum', 'role:super_admin,staff'])->group(function (): void {
    Route::get('/dashboard', fn () => response()->json(['status' => 'ready']));
    Route::get('/businesses', [BusinessApprovalController::class, 'index']);
    Route::patch('/businesses/{business}', [BusinessApprovalController::class, 'update']);
    Route::get('/products', [ProductApprovalController::class, 'index']);
    Route::patch('/products/{product}', [ProductApprovalController::class, 'update']);
    Route::post('/orders/{order}/delivery-assignment', [DeliveryAssignmentController::class, 'store']);
    Route::get('/settlements', [SettlementController::class, 'index']);
    Route::post('/settlements', [SettlementController::class, 'store']);
    Route::post('/settlements/{settlement}/pay', [SettlementController::class, 'pay']);
    Route::get('/content/pages', [ContentController::class, 'pages']);
    Route::post('/content/pages/{page?}', [ContentController::class, 'savePage']);
    Route::get('/content/banners', [ContentController::class, 'banners']);
    Route::post('/content/banners/{banner?}', [ContentController::class, 'saveBanner']);
});
