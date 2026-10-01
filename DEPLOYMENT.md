# Deploying TROV (real pilot)

TROV is two things that deploy differently:

1. **The web app** (this repository) — a Laravel + MySQL server that editors and
   owners sign in to.
2. **The desktop monitoring component** (`desktop-agent/`) — a small program that
   runs on each editor's computer and uploads screenshots. It is *not* hosted; it
   is installed per machine and pointed at the web app's URL.

Both must speak over **HTTPS** — the agent uploads personal screenshots, so plain
HTTP is not acceptable.

---

## Requirements on the server

- PHP 8.3 or newer, with extensions: pdo_mysql, mbstring, openssl, tokenizer,
  xml, ctype, json, fileinfo, curl, zip, gd
- Composer
- Node.js 18+ and npm (to build front-end assets)
- **MySQL 8** (not MariaDB — the migrations use the `utf8mb4_0900_ai_ci`
  collation, which MariaDB does not have)
- A domain name and a TLS certificate (Let's Encrypt is free)

---

## Option A — Laravel Cloud (smoothest for this stack)

1. Push this repo to GitHub/GitLab.
2. Create a project on https://cloud.laravel.com and connect the repo.
3. Add a MySQL 8 database in the dashboard; it wires `DB_*` for you.
4. Set the env vars from `.env.production.example` (it generates `APP_KEY`).
5. Set the deploy/build commands:
   `composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan migrate --force && php artisan storage:link`
6. Deploy. Point your domain at it; TLS is managed for you.

## Option B — VPS + Laravel Forge (full control)

1. Get a small VPS (Ubuntu 24.04, ~$5–10/mo: Hetzner, DigitalOcean, Linode).
2. Connect it to https://forge.laravel.com, create a site for your domain.
3. In Forge: create a MySQL 8 database and user; enable the free Let's Encrypt
   certificate for the domain.
4. Point the site's repository at this repo. Set the deploy script to:
   ```
   cd $FORGE_SITE_PATH
   git pull origin main
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
5. Fill the site's `.env` from `.env.production.example`, then run
   `php artisan key:generate` once.
6. Deploy.

## Option C — Manual VPS (no Forge)

Install nginx + php-fpm + MySQL 8, clone the repo to `/var/www/trov`, then:

```bash
cp .env.production.example .env        # fill in real values
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache
```

Point nginx at `public/`, add the Let's Encrypt certificate (`certbot`), and
force HTTPS.

---

## First-run data

Do **not** run the demo seeder in production (it creates accounts with the
password `password`). Instead create the real owner account once, e.g. via
`php artisan tinker`:

```php
\App\Models\User::forceCreate([
  'role' => 'Owner', 'name' => 'Aileen Villa', 'email' => 'aileen@kpc.co',
  'password_hash' => bcrypt('a-strong-password'), 'is_active' => true,
]);
```

Then add the editors the same way (role `'Video Editor'`), or build the owner
account-management screen first.

---

## Scheduler (required for retention cleanup)

TROV uses the Laravel scheduler to delete old screenshots nightly. Add this one
cron entry on the server so the scheduler runs:

```
* * * * * cd /var/www/trov && php artisan schedule:run >> /dev/null 2>&1
```

On **Laravel Forge** use the site's "Scheduler" tab (it adds this for you). On
**Laravel Cloud** enable the scheduler in the project settings. Without it,
screenshots are never pruned.

---

## The desktop agent on each editor's machine

1. Install Python 3 and the dependencies: `pip install requests mss pynput`.
2. Run it pointed at the live server:
   ```
   python trov_agent.py --server https://trov.example.com --email editor@kpc.co
   ```
   (password via the `TROV_PASSWORD` env var or the prompt).
3. For non-technical editors, package it as a single `.exe` with PyInstaller so
   it is a double-click, and have it start when they log in.

The agent only captures while the editor has an open session in the web app, so
nothing is collected off the clock.

---

## Before you take real screenshots — important

- **Consent.** This system records employees' screens. Get each editor's
  informed consent and tell them what is captured, how long it is kept, and who
  can see it. This is both right and, in many places, legally required.
- **Retention.** Screenshots are kept 14 days then deleted automatically by the
  `captures:prune` command, which is scheduled to run nightly at 01:00. For that
  schedule to fire, the server needs the Laravel scheduler cron entry (see
  "Scheduler" below). You can also run it by hand: `php artisan captures:prune`
  (or `--days=N` for a different retention period).
- **Backups.** Back up the database and the `storage/app/private` folder; the
  screenshots are only on the server's disk.
