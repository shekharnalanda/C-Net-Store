@extends('layouts.storefront')
@section('title', $product->name.' | C-Net Store')
@section('content')
<section class="container product-detail"><div class="product-visual">@if($product->image_url)<img src="{{ $product->image_url }}" alt="{{ $product->name }}">@else{{ $product->product_type === 'food' ? '🍲' : ($product->product_type === 'grocery' ? '🥦' : '📦') }}@endif</div><div>
<a class="muted" href="{{ route('businesses.show', $product->business) }}">{{ $product->business->name }}</a><h1>{{ $product->name }}</h1>
<p>{{ $product->description ?: 'Available from a trusted C-Net Store seller in Bihar Sharif.' }}</p>
<div class="detail-price">₹{{ number_format($product->sale_price ?? $product->price, 2) }} @if($product->sale_price)<del>₹{{ number_format($product->price, 2) }}</del>@endif</div>
<p class="stock">{{ $product->stock_quantity > 0 ? '✓ In stock' : 'Currently unavailable' }}</p>
<button id="add-to-cart" class="btn primary" type="button" data-product="{{ $product->id }}" data-outlet="{{ optional($product->business->outlets->first())->id }}" @disabled($product->stock_quantity < 1)>Add to Cart</button>
<div id="cart-status" class="form-status" aria-live="polite"></div><div class="delivery-note">📍 Delivery available in Bihar Sharif<br>🔒 Secure online payment only</div>
</div></section>
@if($related->isNotEmpty())<section class="section container"><div class="section-head"><h2>You may also like</h2></div><div class="product-grid">@foreach($related as $item) @include('storefront.partials.product-card', ['product' => $item]) @endforeach</div></section>@endif
@endsection
@push('scripts')
<script src="{{ asset('js/storefront-product.js') }}" defer></script>
@endpush
