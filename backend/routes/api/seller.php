<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Seller\BusinessController;
use App\Http\Controllers\Api\Seller\ProductController;

Route::middleware(['auth:sanctum', 'role:seller'])->group(function (): void {
    Route::get('/dashboard', fn () => response()->json(['status' => 'ready']));
    Route::apiResource('businesses', BusinessController::class)->only(['index', 'store', 'show']);
    Route::apiResource('products', ProductController::class)->only(['index', 'store', 'update']);
});
