@extends('layouts.storefront')
@section('title', 'My Orders | C-Net Store')
@section('content')
<section class="page-head"><div class="container"><h1>My Orders</h1><p>Track your purchases, payments and delivery status.</p></div></section>
<section class="container" style="padding:32px 0 56px">
    <div id="orders-loading" class="cart-panel">Loading your orders…</div>
    <div id="orders-error" class="cart-panel form-status" hidden></div>
    <div id="orders-list" style="display:grid;gap:18px"></div>
    <div id="orders-empty" class="empty-cart" hidden>
        <div>📦</div><h2>No orders yet</h2><p>Your completed checkouts will appear here.</p>
        <a class="btn primary" href="{{ route('catalog') }}">Start Shopping</a>
    </div>
</section>
@endsection
@push('scripts')
<script src="{{ asset('js/storefront-orders.js') }}?v=20260828" defer></script>
@endpush
