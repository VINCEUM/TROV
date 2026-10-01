# TROV

**Development of an Integrated Workplace Management System for Kingdom Production Company**

TROV is a workplace management console for Kingdom Production Company (KPC), a
video production agency. It replaces a manual, chat-based routine with a single
system that verifies a daily devotional before a shift can begin, records time
in and time out, tracks the client project each editor is working on, monitors
ongoing work, and captures end-of-day progress.

## Roles

- **Owner** — manages accounts, clients, projects and tasks, and reviews
  devotionals, attendance, live status, screenshots and reports.
- **Video Editor** — submits the daily devotional, times in and out, works on
  assigned tasks, and views their own records.

## Technology

- PHP 8.5 with the Laravel framework
- Livewire and Blade for the server-driven interface
- Tailwind-based styling compiled with Vite
- MySQL 8 for structured records

## Requirements

- PHP 8.2+ and Composer
- Node.js 18+ and npm
- A MySQL 8 server

## Setup

```sh
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Set the database connection in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=trov
DB_USERNAME=root
DB_PASSWORD=
```

Create the schema and sample data, then link storage for uploaded files:

```sh
php artisan migrate --seed
php artisan storage:link
```

## Running

Build the front-end assets and start the server:

```sh
npm run build
php artisan serve
```

Then open http://127.0.0.1:8000.

During active development you can run the asset watcher instead of building:

```sh
npm run dev
```

## Demo accounts

Every seeded account uses the password `password`.

| Role         | Email             |
| ------------ | ----------------- |
| Owner        | aileen@kpc.test   |
| Owner        | jemeul@kpc.test   |
| Video Editor | vince@kpc.test    |

Any editor is `firstname@kpc.test` (for example `rosie@kpc.test`).

## Project structure

- `app/Models` — Eloquent models for the ten domain entities.
- `app/Livewire` — the interactive editor workspace and owner admin panel.
- `app/Http/Controllers` — authentication and page controllers.
- `database/migrations` — the schema, one migration per table.
- `resources/views` — the Blade screens and the shared window shell.
- `resources/css/app.css` — the TROV design system.
