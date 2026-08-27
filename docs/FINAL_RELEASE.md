# C-Net Store Final Release Handover

## Verified release state

- Production storefront: https://cnetstore.mciedu.com
- Laravel health: https://cnetstore.mciedu.com/up
- Admin login: https://cnetstore.mciedu.com/admin/login
- HTTPS redirect, public storefront routes, API health, and security headers are checked by `Final Release Audit`.
- Backend syntax, tests, locked dependency validation, and Composer security advisories are checked on every main-branch push.
- Customer, Seller, and Delivery Flutter apps are analyzed and built by `Mobile Apps CI`.
- Production database migrations and framework runtime tables are installed on BigRock cPanel.

## Server operations

Configure these in cPanel and keep their output/logs under regular review:

1. Scheduler, once per minute:

   ```cron
   * * * * * /opt/cpanel/ea-php83/root/usr/bin/php /home4/mcied45x/repositories/C-Net-Store/backend/artisan schedule:run >> /dev/null 2>&1
   ```

2. Queue fallback, once per minute:

   ```cron
   * * * * * /opt/cpanel/ea-php83/root/usr/bin/php /home4/mcied45x/repositories/C-Net-Store/backend/artisan queue:work --stop-when-empty --tries=3 >> /home4/mcied45x/repositories/C-Net-Store/backend/storage/logs/queue.log 2>&1
   ```

3. Enable automated account/database backups in cPanel and verify a restoration periodically.
4. Keep AutoSSL renewal enabled for `cnetstore.mciedu.com`.
5. Monitor `backend/storage/logs/laravel.log`, queue failures, payment webhooks, and GitHub Actions.

## External release inputs

These values must stay in provider consoles or encrypted CI/cPanel secrets, never in Git:

- Razorpay live keys and webhook signing secret
- SMTP production credentials
- Firebase Android/iOS configuration
- Google Maps API keys with package/domain restrictions
- Android upload keystore and Play signing configuration
- Apple signing certificates, provisioning profile, and App Store Connect access

Until signing credentials are configured, mobile CI artifacts are validation/debug builds and are not store-ready signed releases.

## Deployment and rollback

1. Deploy an exact tested commit from the cPanel Git interface.
2. Run migrations, optimization, production preflight, and smoke tests via `.cpanel.yml`.
3. If a release fails, redeploy the last known-good commit and run `php artisan optimize:clear`.
4. Restore the database only from a verified backup and only when schema/data rollback is necessary.

## Release gate

A release is approved when Backend CI, Mobile Apps CI (for mobile changes), and Final Release Audit are green, production preflight passes, and required provider credentials are configured for the target channel.
