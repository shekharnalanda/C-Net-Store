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
<script>
document.getElementById('customer-login').addEventListener('submit',async function(event){
event.preventDefault();const button=this.querySelector('button'),status=document.getElementById('login-status');button.disabled=true;status.className='form-status';status.textContent='Signing in…';
try{const result=await CNet.api('/login',{method:'POST',body:JSON.stringify({login:document.getElementById('login-value').value.trim(),password:document.getElementById('login-password').value,device_name:'C-Net Store Website'})});if(result.user.role!=='customer')throw new Error('Please use a customer account on this website.');CNet.setSession(result.token,result.user);status.className='form-status success';status.textContent='Login successful.';const redirect=new URLSearchParams(location.search).get('redirect')||'/shop';location.assign(redirect.startsWith('/')?redirect:'/shop');}
catch(error){status.className='form-status error';status.textContent=error.message;button.disabled=false;}
});
</script>
@endpush
