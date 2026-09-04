<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\StorefrontController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OperationsController as AdminOperationsController;
use App\Http\Controllers\Admin\ProductImageLibraryController as AdminProductImageLibraryController;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/shop', [StorefrontController::class, 'catalog'])->name('catalog');
Route::get('/products/{product}', [StorefrontController::class, 'product'])->name('products.show');
Route::get('/stores/{business}', [StorefrontController::class, 'business'])->name('businesses.show');
Route::view('/login', 'storefront.login')->name('login');
Route::get('/cart', [StorefrontController::class, 'cart'])->name('cart');
Route::view('/orders', 'storefront.orders')->name('customer.orders');
Route::redirect(
    '/app/android',
    'https://github.com/shekharnalanda/C-Net-Store/releases/latest/download/C-Net-Store-Customer.apk'
)->name('app.android');
Route::redirect('/app/customer', 'https://github.com/shekharnalanda/C-Net-Store/releases/latest/download/C-Net-Store-Customer.apk')->name('app.customer');
Route::redirect('/app/seller', 'https://github.com/shekharnalanda/C-Net-Store/releases/latest/download/C-Net-Store-Seller.apk')->name('app.seller');
Route::redirect('/app/delivery', 'https://github.com/shekharnalanda/C-Net-Store/releases/latest/download/C-Net-Store-Delivery-Partner.apk')->name('app.delivery');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });
    Route::middleware('admin')->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/sellers', [AdminOperationsController::class, 'sellers'])->name('sellers');
        Route::get('/products', [AdminOperationsController::class, 'products'])->name('products');
        Route::get('/product-image-library', [AdminProductImageLibraryController::class, 'index'])->name('image-library.index');
        Route::post('/product-image-library', [AdminProductImageLibraryController::class, 'store'])->name('image-library.store');
        Route::patch('/product-image-library/{asset}', [AdminProductImageLibraryController::class, 'update'])->name('image-library.update');
        Route::delete('/product-image-library/{asset}', [AdminProductImageLibraryController::class, 'destroy'])->name('image-library.destroy');
        Route::get('/orders', [AdminOperationsController::class, 'orders'])->name('orders');
        Route::get('/customers', [AdminOperationsController::class, 'customers'])->name('customers');
        Route::get('/delivery', [AdminOperationsController::class, 'delivery'])->name('delivery');
        Route::get('/settlements', [AdminOperationsController::class, 'settlements'])->name('settlements');
        Route::get('/support', [AdminOperationsController::class, 'support'])->name('support');
        Route::get('/content', [AdminOperationsController::class, 'content'])->name('content');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
    });
});


// Customer password recovery.
Route::middleware('throttle:5,1')->group(function (): void {
    Route::get('/forgot-password', function () {
        return view('storefront.forgot-password');
    });

    Route::post('/forgot-password', function (\Illuminate\Http\Request $request) {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = \App\Models\User::query()->where('email', $data['email'])->first();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => \Illuminate\Support\Facades\Hash::make($token), 'created_at' => now()]
            );
            $url = url('/reset-password/'.$token).'?email='.urlencode($user->email);
            \Illuminate\Support\Facades\Mail::raw(
                "Use this secure link to reset your C-Net Store password. The link expires in 60 minutes.\n\n".$url,
                fn ($message) => $message->to($user->email)->subject('Reset your C-Net Store password')
            );
        }

        return back()->with('status', 'If this email is registered, a password reset link has been sent.');
    });

    Route::get('/reset-password/{token}', function (string $token, \Illuminate\Http\Request $request) {
        return view('storefront.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    });

    Route::post('/reset-password', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
        ]);
        $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $data['email'])->first();
        $valid = $record
            && \Illuminate\Support\Facades\Hash::check($data['token'], $record->token)
            && \Illuminate\Support\Carbon::parse($record->created_at)->greaterThan(now()->subMinutes(60));

        if (! $valid) {
            return back()->withErrors(['email' => 'This password reset link is invalid or has expired.'])->withInput($request->only('email'));
        }

        $user = \App\Models\User::query()->where('email', $data['email'])->firstOrFail();
        $user->forceFill(['password' => \Illuminate\Support\Facades\Hash::make($data['password'])])->save();
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect('/login')->with('status', 'Password changed successfully. Please login.');
    });
});

Route::get("/sitemap.xml", [\App\Http\Controllers\Web\SeoController::class, "sitemap"])->name("sitemap");
