# AGENTS.md

## Cursor Cloud specific instructions

### Overview

BioGlow Solutions (BGS) is a Laravel 12 healthcare/aesthetics clinic management platform with multiple portals: public website, admin dashboard, patient portal, doctor portal (Blade + external React SPA), POS (external React SPA), and inventory (external React SPA). The backend is a PHP monolith; external SPAs live in separate repos.

### System dependencies (pre-installed in snapshot)

- **PHP 8.2** with extensions: cli, curl, mbstring, mysql, xml, zip, gd, sqlite3, bcmath, intl, readline (installed via `ppa:ondrej/php`)
- **Composer** (installed at `/usr/local/bin/composer`)
- **MySQL 8.0** (database: `lms_practice_one`, root user, no password)
- **Node.js 22.x / npm 10.x**

### Starting services

MySQL must be started before the app:
```bash
sudo service mysql start
```

The `composer dev` script runs three processes concurrently (Laravel server, queue worker, Vite HMR):
```bash
composer dev
```

Or run them individually:
```bash
php artisan serve --host=0.0.0.0 --port=8000
php artisan queue:listen --tries=1
npm run dev
```

### Key credentials (seeded via `php artisan db:seed`)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@gmail.com | 12345678 |
| Super Admin | superadmin@gmail.com | 12345678 |
| Head Nurse | headnurse@gmail.com | 12345678 |

### Lint / Test / Build

- **Lint:** `./vendor/bin/pint --test` (Laravel Pint — 7 pre-existing style issues)
- **Tests:** `php artisan test` (PHPUnit; uses SQLite `:memory:` — no MySQL needed. Note: 29 feature tests fail due to a pre-existing `NOW()` MySQL-only function used in migration `2026_05_11_100000_add_staff_approval_fields_to_admins_table.php`; unit tests pass.)
- **Build:** `npm run build` (Vite production build)

### Gotchas

- The `.env` must have `SESSION_DOMAIN=` (empty) for local dev. Setting it to `localhost` when using `127.0.0.1` causes 419 CSRF errors.
- Seeded admin accounts are created with `status=draft`. They are auto-approved via the `AdminSeeder` using `updateOrCreate`, but if running seeds for the first time and the migration backfills status as `draft`, you may need to run `php artisan db:seed` again or manually approve via tinker: `Admin::where('email','admin@gmail.com')->update(['status'=>'approved','approved_at'=>now()])`.
- Queue, cache, and sessions all use the `database` driver — no Redis required.
- Mail defaults to `failover` mailer (falls back to `log` if SMTP is not configured) — the app won't crash without SMTP credentials.
