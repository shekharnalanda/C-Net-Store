<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\StorefrontController;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/shop', [StorefrontController::class, 'catalog'])->name('catalog');
Route::get('/products/{product}', [StorefrontController::class, 'product'])->name('products.show');
Route::get('/stores/{business}', [StorefrontController::class, 'business'])->name('businesses.show');
Route::view('/login', 'storefront.login')->name('login');
Route::get('/cart', [StorefrontController::class, 'cart'])->name('cart');
