@extends('layouts.storefront')

@section('title', 'Forgot Password | C-Net Store')

@section('content')
<section class="auth-shell">
    <div class="auth-card">
        <h1>Reset your password</h1>
        <p>Enter your registered email. We will send a secure reset link.</p>
        @if (session('status'))<div class="notice success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="notice error">{{ $errors->first() }}</div>@endif
        <form method="POST" action="/forgot-password">
            @csrf
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
            <button type="submit">Send reset link</button>
        </form>
        <p><a href="/login">Back to customer login</a></p>
    </div>
</section>
@endsection
