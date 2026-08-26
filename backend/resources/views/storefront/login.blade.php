@extends('layouts.storefront')
@section('title', 'Login | C-Net Store')
@section('content')<section class="auth-wrap"><div class="auth-card"><img src="{{ asset('images/cnet-store-logo.png') }}" alt="C-Net Store"><h1>Welcome back</h1><p>Login using your mobile number or email.</p><form><label>Mobile number or email<input type="text" autocomplete="username" required></label><label>Password<input type="password" autocomplete="current-password" required></label><button class="btn primary" type="submit">Login</button></form><a href="#">Create a customer account</a></div></section>@endsection

