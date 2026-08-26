<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'service' => 'C-Net Store API',
        'cod_enabled' => false,
    ]));

    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('customer')->group(base_path('routes/api/customer.php'));
    Route::prefix('seller')->group(base_path('routes/api/seller.php'));
    Route::prefix('delivery')->group(base_path('routes/api/delivery.php'));
    Route::prefix('admin')->group(base_path('routes/api/admin.php'));
});
