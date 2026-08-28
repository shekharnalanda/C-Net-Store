document.addEventListener('DOMContentLoaded', async () => {
    const loading = document.getElementById('orders-loading');
    const error = document.getElementById('orders-error');
    const list = document.getElementById('orders-list');
    const empty = document.getElementById('orders-empty');

    if (!window.CNet.token) {
        window.location.href = '/login?next=/orders';
        return;
    }

    const money = value => '₹' + Number(value || 0).toFixed(2);
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[char]);

    try {
        const response = await window.CNet.api('/customer/orders');
        const orders = response.data || [];
        loading.hidden = true;

        if (!orders.length) {
            empty.hidden = false;
            return;
        }

        list.innerHTML = orders.map(order => {
            const payment = (order.payments || [])[0];
            const items = (order.items || []).map(item =>
                '<li>' + escapeHtml(item.product?.name || item.product_name || 'Product') +
                ' × ' + escapeHtml(item.quantity) + '</li>'
            ).join('');
            const placedAt = order.placed_at ? new Date(order.placed_at).toLocaleString('en-IN') : '';
            return '<article class="cart-panel">' +
                '<div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap">' +
                    '<div><strong>Order ' + escapeHtml(order.order_number) + '</strong>' +
                    '<p style="margin:6px 0;color:#5f6b7a">' + escapeHtml(order.business?.name || 'C-Net Store') +
                    (placedAt ? ' · ' + escapeHtml(placedAt) : '') + '</p></div>' +
                    '<div style="text-align:right"><strong>' + money(order.grand_total) + '</strong>' +
                    '<p style="margin:6px 0;color:#0756c9;text-transform:capitalize">' + escapeHtml(order.status) + '</p></div>' +
                '</div>' +
                '<ul style="margin:12px 0;padding-left:20px">' + items + '</ul>' +
                '<div style="border-top:1px solid #e5e7eb;padding-top:12px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">' +
                    '<span>Payment: <strong style="text-transform:capitalize">' + escapeHtml(payment?.status || 'pending') + '</strong></span>' +
                    (payment?.provider_payment_id ? '<span>Payment ID: ' + escapeHtml(payment.provider_payment_id) + '</span>' : '') +
                '</div></article>';
        }).join('');
    } catch (err) {
        loading.hidden = true;
        error.hidden = false;
        error.textContent = err.message;
        if (!window.CNet.token) setTimeout(() => { window.location.href = '/login?next=/orders'; }, 900);
    }
});
