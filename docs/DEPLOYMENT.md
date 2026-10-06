# Deployment checklist (Hostinger + Cloudflare)

Verify on your actual plan first (do not assume): PHP >= 8.2, MySQL, SSH, Composer, cron, ability to set the
document root of a subdomain to `public/`, required PHP extensions.

1. Create subdomain `api.yourdomain.com`; document root -> `Upadhyay-Backend/public`.
2. `git clone` the backend repo outside the public web root if possible; `composer install --no-dev -o`.
3. Create MySQL DB + user in hPanel; fill `.env` (see `.env.example`). Never commit `.env`.
4. `php artisan key:generate --force` then `php artisan migrate --force`.
5. Set `APP_ENV=production`, `APP_DEBUG=false`, `FRONTEND_URL=https://yourdomain.com`.
6. R2: create private bucket + token; set `FILESYSTEM_DISK=r2` and `R2_*`.
7. Cloudflare: proxy the `api` record, SSL mode Full (strict).
8. `php artisan config:cache && php artisan route:cache`.
9. Cron (confirm Hostinger syntax): `* * * * * php /home/USER/path/artisan schedule:run >> /dev/null 2>&1`
10. Smoke test: `GET https://api.yourdomain.com/api/v1/health`, then test CORS from the frontend.
