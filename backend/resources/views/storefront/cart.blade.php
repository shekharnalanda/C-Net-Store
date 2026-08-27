@extends('layouts.storefront')
@section('title', 'My Cart | C-Net Store')
@section('content')
<section class="page-head"><div class="container"><h1>My Cart</h1><p>Review your items before secure online checkout.</p></div></section>
<section class="container cart-shell">
<div id="cart-loading" class="cart-panel">Loading your secure cart…</div>
<div id="cart-content" hidden><div class="cart-panel"><div id="cart-items"></div></div>
<aside class="cart-panel checkout-panel"><h2>Order summary</h2><div class="summary-line"><span>Subtotal</span><strong id="cart-total">₹0.00</strong></div>
<label>Delivery address<select id="address-select"></select></label>
<label>Payment method<select id="payment-method"><option value="upi">UPI</option><option value="card">Card</option><option value="netbanking">Net banking</option><option value="wallet">Wallet</option></select></label>
<button id="checkout-button" class="btn primary" type="button">Pay securely with Razorpay</button><div id="checkout-status" class="form-status" aria-live="polite"></div></aside></div>
<div id="cart-empty" class="empty-cart" hidden><div>🛒</div><h2>Your cart is empty</h2><p>Add products, groceries or restaurant food to continue.</p><a class="btn primary" href="{{ route('catalog') }}">Start Shopping</a></div>
</section>
@endsection
@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const money=value=>'₹'+Number(value).toFixed(2);let activeCart=null;
function escapeHtml(value){const div=document.createElement('div');div.textContent=value||'';return div.innerHTML;}
function showEmpty(){document.getElementById('cart-loading').hidden=true;document.getElementById('cart-empty').hidden=false;}
async function loadCart(){
if(!CNet.token){location.assign('/login?redirect=/cart');return;}const cartId=localStorage.getItem('cnet_cart_id');if(!cartId){showEmpty();return;}
try{const [cartResult,addressResult]=await Promise.all([CNet.api('/customer/carts/'+cartId),CNet.api('/customer/addresses')]);activeCart=cartResult.data;if(!activeCart.items||!activeCart.items.length){showEmpty();return;}
let total=0,count=0;document.getElementById('cart-items').innerHTML=activeCart.items.map(item=>{const price=Number(item.product.sale_price||item.product.price);total+=price*item.quantity;count+=Number(item.quantity);return '<div class="cart-item"><div class="cart-icon">📦</div><div><strong>'+escapeHtml(item.product.name)+'</strong><small>Quantity: '+item.quantity+'</small></div><strong>'+money(price*item.quantity)+'</strong></div>';}).join('');
document.getElementById('cart-total').textContent=money(total);localStorage.setItem('cnet_cart_count',String(count));CNet.updateHeader();
const addresses=addressResult.data||[],select=document.getElementById('address-select');select.innerHTML=addresses.map(a=>'<option value="'+a.id+'">'+escapeHtml(a.label+' — '+a.address_line+', '+a.city)+'</option>').join('');if(!addresses.length)throw new Error('Please add a delivery address in the Customer App before checkout.');
document.getElementById('cart-loading').hidden=true;document.getElementById('cart-content').hidden=false;
}catch(error){document.getElementById('cart-loading').textContent=error.message;}}
document.getElementById('checkout-button').addEventListener('click',async function(){
const status=document.getElementById('checkout-status'),addressId=Number(document.getElementById('address-select').value),outletId=Number(localStorage.getItem('cnet_outlet_id'));this.disabled=true;status.className='form-status';status.textContent='Creating secure order…';
try{const checkout=await CNet.api('/customer/checkout',{method:'POST',body:JSON.stringify({cart_id:activeCart.id,address_id:addressId,outlet_id:outletId,payment_method:document.getElementById('payment-method').value,fulfilment_type:'cnet_delivery'})});const order=checkout.data;
const payment=await CNet.api('/customer/orders/'+order.id+'/payment',{method:'POST',body:'{}'}),gateway=payment.data;
new Razorpay({key:gateway.key_id,amount:gateway.amount,currency:gateway.currency,name:'C-Net Store',description:'Order '+order.order_number,order_id:gateway.provider_order_id,prefill:{name:CNet.user?.name||'',email:CNet.user?.email||'',contact:CNet.user?.phone||''},theme:{color:'#0756c9'},
handler:async function(response){status.textContent='Verifying payment…';try{await CNet.api('/customer/orders/'+order.id+'/payment/verify',{method:'POST',body:JSON.stringify(response)});localStorage.removeItem('cnet_cart_id');localStorage.removeItem('cnet_outlet_id');localStorage.setItem('cnet_cart_count','0');CNet.updateHeader();document.getElementById('cart-content').innerHTML='<div class="payment-success"><div>✓</div><h2>Payment successful</h2><p>Order <strong>'+escapeHtml(order.order_number)+'</strong> is confirmed.</p><a class="btn primary" href="/shop">Continue shopping</a></div>';}catch(error){status.className='form-status error';status.textContent=error.message;}},
modal:{ondismiss:()=>{status.className='form-status error';status.textContent='Payment was not completed. You can try again.';document.getElementById('checkout-button').disabled=false;}}}).open();status.textContent='Opening Razorpay…';
}catch(error){status.className='form-status error';status.textContent=error.message;this.disabled=false;}});
loadCart();
</script>
@endpush
