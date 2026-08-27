# C-Net Store Final Release Handover

## Verified release state

- Production storefront: https://cnetstore.mciedu.com
- Laravel health: https://cnetstore.mciedu.com/up
- Admin login: https://cnetstore.mciedu.com/admin/login
- Backend CI, Mobile Apps CI, and Final Release Audit are operational.
- Production migrations, HTTPS, security headers, storefront routes, and API health are verified.

## Active BigRock operations

Activated on 27 August 2026:

- Laravel scheduler: every minute
- Database queue worker: every minute, stop when empty
- Database and public-storage backup: daily at 02:30 server time
- Public health monitor: every 10 minutes
- Backup retention: 14 days
- Backup directory: `/home4/mcied45x/backups/cnet-store`
- Scheduler log: `backend/storage/logs/scheduler.log`
- Queue log: `backend/storage/logs/queue.log`
- Health log: `backend/storage/logs/health-monitor.log`

The host reports that provider-generated account backups are not enabled. Application-level database and uploaded-file backups therefore run through `scripts/backup-production.sh`. Periodically copy a verified backup off the hosting account and test restoration.

## External release inputs

Keep these only in provider consoles or encrypted CI/cPanel secrets:

- Razorpay live keys and webhook signing secret
- SMTP production credentials
- Firebase Android/iOS configuration
- Restricted Google Maps API keys
- Android upload keystore and Play signing
- Apple signing and App Store Connect access

Mobile CI artifacts remain validation/debug builds until signing credentials are configured.

## Deployment and rollback

1. Deploy an exact tested commit through cPanel Git.
2. Let `.cpanel.yml` run migrations, optimization, production preflight, and smoke tests.
3. On failure, redeploy the last known-good commit and run `php artisan optimize:clear`.
4. Restore data only from a verified backup when schema/data rollback is necessary.

## Release gate

Approve a release only when applicable CI workflows are green, production preflight passes, live monitoring is healthy, and required provider credentials are configured for the target channel.
