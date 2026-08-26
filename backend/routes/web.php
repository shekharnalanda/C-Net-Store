<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\StorefrontController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OperationsController as AdminOperationsController;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/shop', [StorefrontController::class, 'catalog'])->name('catalog');
Route::get('/products/{product}', [StorefrontController::class, 'product'])->name('products.show');
Route::get('/stores/{business}', [StorefrontController::class, 'business'])->name('businesses.show');
Route::view('/login', 'storefront.login')->name('login');
Route::get('/cart', [StorefrontController::class, 'cart'])->name('cart');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });
    Route::middleware('admin')->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/sellers', [AdminOperationsController::class, 'sellers'])->name('sellers');
        Route::get('/products', [AdminOperationsController::class, 'products'])->name('products');
        Route::get('/orders', [AdminOperationsController::class, 'orders'])->name('orders');
        Route::get('/customers', [AdminOperationsController::class, 'customers'])->name('customers');
        Route::get('/delivery', [AdminOperationsController::class, 'delivery'])->name('delivery');
        Route::get('/settlements', [AdminOperationsController::class, 'settlements'])->name('settlements');
        Route::get('/support', [AdminOperationsController::class, 'support'])->name('support');
        Route::get('/content', [AdminOperationsController::class, 'content'])->name('content');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
    });
});
