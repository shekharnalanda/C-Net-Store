@extends('layouts.storefront')

@section('title', 'Choose New Password | C-Net Store')

@section('content')
<section class="auth-shell">
    <div class="auth-card">
        <h1>Choose a new password</h1>
        <p>Use at least 8 characters.</p>
        @if ($errors->any())<div class="notice error">{{ $errors->first() }}</div>@endif
        <form method="POST" action="/reset-password">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required>
            <label for="password">New password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            <button type="submit">Change password</button>
        </form>
    </div>
</section>
@endsection
