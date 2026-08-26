@extends('layouts.storefront')
@section('title', 'My Cart | C-Net Store')
@section('content')<section class="page-head"><div class="container"><h1>My Cart</h1><p>Review your items before secure online checkout.</p></div></section><section class="container empty-cart"><div>🛒</div><h2>Your cart is empty</h2><p>Add products, groceries or restaurant food to continue.</p><a class="btn primary" href="{{ route('catalog') }}">Start Shopping</a></section>@endsection

