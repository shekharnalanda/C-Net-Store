<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Seller\BusinessController;
use App\Http\Controllers\Api\Seller\ProductController;
use App\Http\Controllers\Api\Seller\OrderController;
use App\Http\Controllers\Api\Seller\ProductImageLibraryController;

Route::middleware(['auth:sanctum', 'role:seller'])->group(function (): void {
    Route::get('/dashboard', fn () => response()->json(['status' => 'ready']));
    Route::apiResource('businesses', BusinessController::class)->only(['index', 'store', 'show'])->names(['index' => 'seller.businesses.index', 'store' => 'seller.businesses.store', 'show' => 'seller.businesses.show']);
    Route::apiResource('products', ProductController::class)->only(['index', 'store', 'update']);
    Route::get('/product-image-library/groups', [ProductImageLibraryController::class, 'groups']);
    Route::get('/product-image-library', [ProductImageLibraryController::class, 'index']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::patch('/orders/{order}', [OrderController::class, 'update']);
});
