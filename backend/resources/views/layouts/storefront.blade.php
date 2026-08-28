<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="@yield('meta_description', 'Shop products, groceries and restaurant food from trusted local stores in Bihar Sharif with C-Net Store.')">
<meta name="theme-color" content="#0756c9"><title>@yield('title', 'C-Net Store | Shopping, Grocery & Food Delivery')</title>
<link rel="stylesheet" href="{{ asset('css/storefront.css') }}">
</head>
<body>
<header class="site-header">
<div class="topbar"><div class="container topbar-inner"><span>Delivering in <strong>Bihar Sharif</strong></span><span>Help: <a href="tel:+917782801846">7782801846</a></span></div></div>
<div class="container nav-row">
<a class="brand" href="{{ route('home') }}"><img src="{{ asset('images/cnet-store-logo.png') }}" alt="C-Net Store"><span>C-Net Store</span></a>
<form class="search" action="{{ route('catalog') }}"><input name="q" value="{{ request('q') }}" placeholder="Search products, groceries or food"><button aria-label="Search">⌕</button></form>
<nav class="actions"><a id="account-link" href="{{ route('login') }}">Account</a><a href="{{ route('cart') }}">Cart <span class="badge" id="cart-count">0</span></a></nav>
</div>
<nav class="category-nav"><div class="container"><a href="{{ route('catalog', ['type' => 'shopping']) }}">Shopping</a><a href="{{ route('catalog', ['type' => 'grocery']) }}">Grocery</a><a href="{{ route('catalog', ['type' => 'food']) }}">Food Delivery</a><a href="{{ route('catalog') }}">Local Shops</a><a href="#offers">Offers</a></div></nav>
</header>
<main>@yield('content')</main>
<footer class="footer"><div class="container footer-grid"><div><img class="footer-logo" src="{{ asset('images/cnet-store-logo.png') }}" alt="C-Net Store"><p>Your local marketplace for shopping, grocery and food delivery.</p></div><div><h3>Customer Care</h3><p><a href="tel:+917782801846">7782801846</a><br><a href="mailto:mcieducationalgroup@gmail.com">mcieducationalgroup@gmail.com</a></p></div><div><h3>Address</h3><p>MCI Campus, Quamruddin Ganj,<br>Bihar Sharif, Nalanda – 803101</p></div></div><div class="copyright">© {{ date('Y') }} C-Net Store. All rights reserved.</div></footer>
<script src="{{ asset('js/storefront.js') }}?v=20260828" defer></script>
@stack('scripts')
</body></html>
