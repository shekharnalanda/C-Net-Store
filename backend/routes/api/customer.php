<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Customer\AddressController;
use App\Http\Controllers\Api\Customer\CartController;
use App\Http\Controllers\Api\Customer\CheckoutController;
use App\Http\Controllers\Api\Customer\PaymentController;

Route::get('/catalog', fn () => response()->json(['data' => []]));

Route::middleware(['auth:sanctum', 'role:customer'])->group(function (): void {
    Route::apiResource('addresses', AddressController::class)->only(['index', 'store', 'update']);
    Route::get('/carts/{cart}', [CartController::class, 'show']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::delete('/carts/{cart}/items/{item}', [CartController::class, 'removeItem']);
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1');
    Route::post('/orders/{order}/payment', [PaymentController::class, 'create'])->middleware('throttle:10,1');
    Route::post('/orders/{order}/payment/verify', [PaymentController::class, 'verify'])->middleware('throttle:20,1');
});
