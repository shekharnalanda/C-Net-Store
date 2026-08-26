<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'service' => 'C-Net Store API',
        'cod_enabled' => false,
    ]));

    Route::prefix('customer')->group(base_path('routes/api/customer.php'));
    Route::prefix('seller')->group(base_path('routes/api/seller.php'));
    Route::prefix('delivery')->group(base_path('routes/api/delivery.php'));
    Route::prefix('admin')->group(base_path('routes/api/admin.php'));
});

