# Upadhyay-Backend

Laravel REST API for the Upadhyay frontend (separate repo: `Mrkittunaik/Upadhyay`).

```
Frontend (HTML/CSS/JS) --HTTPS--> Laravel API --> MySQL
                                       \--> Cloudflare R2 (files)
```

## Build plan
| Part | Contents | Status |
|------|----------|--------|
| 1 | Structure, config, CORS, Sanctum, error handling, all migrations, models, enums, core services, health route | done |
| 2 | Auth, roles/policies, faculty, company, search, branch requests + approval + 5-digit code, tests | **this zip** |
| 3 | Jobs, applications, invitations, notifications, files (R2), admin, audit, scheduler, full tests, deployment | after |

## Requirements
PHP 8.2+ (ext: mbstring, openssl, pdo_mysql, fileinfo, ctype, json, tokenizer, xml), Composer 2, MySQL 8 / MariaDB 10.6+.

## Local setup
```bash
composer install
cp .env.example .env
php artisan key:generate
# create the MySQL database, set DB_* in .env
php artisan migrate
php artisan db:seed          # dev admin only (prints password if SEED_ADMIN_PASSWORD empty)
php artisan serve            # http://localhost:8000
curl http://localhost:8000/api/v1/health
php artisan test
```

## Key decisions
- **Bearer-token auth** (Sanctum personal access tokens): frontend and API live on different origins, so no cookies/CSRF needed.
- **`job_posts` table / `JobPost` model** instead of `jobs`, to avoid clashing with Laravel's queue `jobs` table. The API route is still `/api/v1/jobs`.
- **Custom `notifications` table** (not Laravel's polymorphic one). `User` uses `Notifiable` only so password-reset mail works; in-app notifications go through `NotificationService`.
- **Roles**: `admin`, `faculty`, `company_main`, `company_branch` (enum `UserRole`), enforced by `role:` middleware.
- **Branch code**: `BranchCodeGenerator` (server-side, 5 digits, unique index as backstop). Used inside the approval transaction in Part 2.
- **Files**: `FileStorageService` validates extension + detected MIME + size, stores under a random UUID name, metadata in `files`. Disk `r2` in production.
- **Responses**: always `{success, message, data|errors, meta?}` via `App\Support\ApiResponse`; API exceptions are rendered as JSON in `bootstrap/app.php`.
- **CORS**: origins from `FRONTEND_URL` (comma separated). No wildcard.
- **Rate limiters** defined in `AppServiceProvider` (`api`, `auth`, `search`, `uploads`, `actions`, `admin`); applied to routes in Parts 2-3.

## Layout
```
app/Enums  app/Models  app/Services  app/Support  app/Policies
app/Http/{Controllers/*,Middleware,Requests}
config/upadhyay.php      project settings (upload rules, pagination)
database/migrations      all 11 migration files
routes/api.php           /api/v1/*
docs/                    API + deployment notes
```

## Cloudflare R2
Create a private bucket + API token, then set `FILESYSTEM_DISK=r2` and `R2_*` in `.env`
(`R2_ENDPOINT=https://<accountid>.r2.cloudflarestorage.com`). Files are served through short-lived signed URLs.

## Hostinger (summary, full notes in docs/DEPLOYMENT.md)
Point `api.yourdomain.com` document root at `public/`. Verify PHP version, SSH, Composer, cron before deploying.
Production: `APP_ENV=production`, `APP_DEBUG=false`, `php artisan migrate --force`, `php artisan config:cache route:cache`.
