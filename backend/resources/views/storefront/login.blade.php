@extends('layouts.storefront')
@section('title', 'Login | C-Net Store')
@section('content')
<section class="auth-wrap"><div class="auth-card">
<img src="{{ asset('images/cnet-store-logo.png') }}" alt="C-Net Store"><h1>Welcome back</h1><p>Login using your mobile number or email.</p>
<form id="customer-login">
<label>Mobile number or email<input id="login-value" type="text" autocomplete="username" required></label>
<label>Password<input id="login-password" type="password" autocomplete="current-password" required></label>
<div id="login-status" class="form-status" aria-live="polite"></div>
<button class="btn primary" type="submit">Login securely</button>
</form><p class="muted">New customer registration is available in the C-Net Store mobile app.</p>
</div></section>
@endsection
@push('scripts')
<script src="{{ asset('js/storefront-login.js') }}" defer></script>
@endpush
