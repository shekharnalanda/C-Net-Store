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
<script>
window.CNet={
token:localStorage.getItem('cnet_token'),user:JSON.parse(localStorage.getItem('cnet_user')||'null'),
api:async function(path,options={}){const headers=Object.assign({'Accept':'application/json','Content-Type':'application/json'},options.headers||{});if(this.token)headers.Authorization='Bearer '+this.token;const response=await fetch('/api/v1'+path,Object.assign({},options,{headers}));const data=await response.json().catch(()=>({}));if(response.status===401){this.clearSession();throw new Error('Please login to continue.')}if(!response.ok){const errors=data.errors?Object.values(data.errors).flat().join(' '):'';throw new Error(errors||data.message||'Something went wrong. Please try again.')}return data;},
setSession:function(token,user){localStorage.setItem('cnet_token',token);localStorage.setItem('cnet_user',JSON.stringify(user));this.token=token;this.user=user;},
clearSession:function(){['cnet_token','cnet_user','cnet_cart_id','cnet_outlet_id','cnet_cart_count'].forEach(key=>localStorage.removeItem(key));this.token=null;this.user=null;},
updateHeader:function(){const account=document.getElementById('account-link'),badge=document.getElementById('cart-count');if(this.user&&account)account.textContent=this.user.name||'Account';if(badge)badge.textContent=localStorage.getItem('cnet_cart_count')||'0';}
};CNet.updateHeader();
</script>
@stack('scripts')
</body></html>
