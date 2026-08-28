<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Customer\AddressController;
use App\Http\Controllers\Api\Customer\CartController;
use App\Http\Controllers\Api\Customer\CheckoutController;
use App\Http\Controllers\Api\Customer\PaymentController;
use App\Http\Controllers\Api\Customer\CancellationController;
use App\Http\Controllers\Api\Customer\ReviewController;
use App\Http\Controllers\Api\Customer\WishlistController;
use App\Http\Controllers\Api\Customer\DeviceController;
use App\Http\Controllers\Api\Customer\SupportController;
use App\Http\Controllers\Api\Customer\OrderController;

Route::get('/catalog', fn () => response()->json(['data' => []]));

Route::middleware(['auth:sanctum', 'role:customer'])->group(function (): void {
    Route::apiResource('addresses', AddressController::class)->only(['index', 'store', 'update']);
    Route::get('/carts/{cart}', [CartController::class, 'show']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::delete('/carts/{cart}/items/{item}', [CartController::class, 'removeItem']);
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders/{order}/payment', [PaymentController::class, 'create'])->middleware('throttle:10,1');
    Route::post('/orders/{order}/payment/verify', [PaymentController::class, 'verify'])->middleware('throttle:20,1');
    Route::post('/orders/{order}/cancel', [CancellationController::class, 'store'])->middleware('throttle:10,1');
    Route::post('/orders/{order}/reviews', [ReviewController::class, 'store']);
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{product}', [WishlistController::class, 'toggle']);
    Route::post('/devices', [DeviceController::class, 'store']);
    Route::get('/support', [SupportController::class, 'index']);
    Route::post('/support', [SupportController::class, 'store']);
    Route::post('/support/{ticket}/reply', [SupportController::class, 'reply']);
});