@extends('layouts.storefront')
@section('title', 'My Cart | C-Net Store')
@section('content')
<section class="page-head"><div class="container"><h1>My Cart</h1><p>Review your items before secure online checkout.</p></div></section>
<section class="container cart-shell">
<div id="cart-loading" class="cart-panel">Loading your secure cart…</div>
<div id="cart-content" hidden><div class="cart-panel"><div id="cart-items"></div></div>
<aside class="cart-panel checkout-panel"><h2>Order summary</h2><div class="summary-line"><span>Subtotal</span><strong id="cart-total">₹0.00</strong></div>
<label>Delivery address<select id="address-select"></select></label>
<label>Payment method<select id="payment-method"><option value="upi">UPI</option><option value="card">Card</option><option value="netbanking">Net banking</option><option value="wallet">Wallet</option></select></label>
<button id="checkout-button" class="btn primary" type="button">Pay securely with Razorpay</button><div id="checkout-status" class="form-status" aria-live="polite"></div></aside></div>
<div id="cart-empty" class="empty-cart" hidden><div>🛒</div><h2>Your cart is empty</h2><p>Add products, groceries or restaurant food to continue.</p><a class="btn primary" href="{{ route('catalog') }}">Start Shopping</a></div>
</section>
@endsection
@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="{{ asset('js/storefront-cart.js') }}" defer></script>
@endpush
