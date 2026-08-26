<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RazorpayWebhookController;
use App\Http\Controllers\Api\PublicContentController;
use App\Http\Controllers\Api\NotificationController;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'service' => 'C-Net Store API',
        'cod_enabled' => false,
    ]));

    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/webhooks/razorpay', RazorpayWebhookController::class)->middleware('throttle:120,1');
    Route::get('/banners', [PublicContentController::class, 'banners']);
    Route::get('/pages/{slug}', [PublicContentController::class, 'page']);
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read']);
    });

    Route::prefix('customer')->group(base_path('routes/api/customer.php'));
    Route::prefix('seller')->group(base_path('routes/api/seller.php'));
    Route::prefix('delivery')->group(base_path('routes/api/delivery.php'));
    Route::prefix('admin')->group(base_path('routes/api/admin.php'));
});
