# C-Net Store Go-Live Status

## Automated readiness

Run these commands from `backend/`:

```bash
php artisan store:preflight --production
php artisan store:catalog-audit
php artisan store:image-library-audit
bash scripts/smoke.sh https://cnetstore.mciedu.com
```

The smoke audit covers the public storefront, catalogue API, policy pages, admin/API authentication boundaries, and all three Android APK download redirects.

## Technical status

- Laravel production environment, database, HTTPS and secure sessions: ready
- Razorpay credentials and webhook secret configuration: ready
- SMTP, queues and scheduled maintenance: ready
- Product image library and catalogue safety rules: ready
- Customer, seller, delivery and admin APIs: protected and reachable
- Customer, seller and delivery Android APK distribution: ready
- Legal and marketplace policy pages: published
- GitHub Backend CI and Final Release Audit: required to pass on every main-branch change

## Business activation remaining

These are operational inputs, not unfinished application code:

1. Enter genuine product prices and stock using the admin CSV workflow.
2. Activate only catalogue items marked Ready to publish.
3. Perform one authorised low-value Razorpay transaction and refund/cancellation drill.
4. Complete one real customer-to-seller-to-delivery order using the three apps.
5. Publish signed apps to Play Store when the store accounts and listing assets are available.

Never invent commercial prices, activate zero-stock items, or run a real payment without explicit approval.
