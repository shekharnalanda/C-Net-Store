# Production deployment

## BigRock cPanel

1. Clone `shekharnalanda/C-Net-Store` into `/home4/mcied45x/repositories/C-Net-Store`.
2. Point `cnetstore.mciedu.com` document root to `/home4/mcied45x/repositories/C-Net-Store/backend/public`.
3. Create `backend/.env` from `.env.example` and fill database, SMTP, Razorpay, Firebase and admin secrets only in cPanel. Never commit this file.
4. Run cPanel Git Version Control **Deploy HEAD Commit**. `.cpanel.yml` installs production dependencies, migrates, seeds the administrator and builds optimized caches.
5. Add this cron job once per minute:

   ```text
   * * * * * cd /home4/mcied45x/repositories/C-Net-Store/backend && /opt/cpanel/ea-php83/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1
   ```

6. Add a continuously supervised queue worker where available. On shared hosting, use this cron fallback:

   ```text
   * * * * * cd /home4/mcied45x/repositories/C-Net-Store/backend && /opt/cpanel/ea-php83/root/usr/bin/php artisan queue:work --stop-when-empty --tries=3 --timeout=90 >> /dev/null 2>&1
   ```

## Go-live checks

- `APP_ENV=production`, `APP_DEBUG=false`, HTTPS and valid `APP_KEY`.
- COD remains disabled and live Razorpay keys/webhook secret are configured.
- `/up` and `/api/v1/health` return HTTP 200.
- Admin login, payment webhook and storage uploads work.
- Scheduler releases expired inventory reservations every five minutes.
- Database backups and cPanel SSL renewal are enabled.
