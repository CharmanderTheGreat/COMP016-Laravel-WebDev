# COMP016-Laravel-WebDev

A Laravel web application.

## Requirements

- PHP (with the extensions Laravel needs — check `composer.json` for the exact version)
- Composer
- Node.js & npm (for frontend assets via Vite)

## Getting Started

Follow these steps after cloning the repo. This project uses SQLite by default, so no separate database server is required.

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Create your environment file

Copy the example environment file:

```bash
cp .env.example .env
```

> On Windows PowerShell, use `Copy-Item .env.example .env` instead.

### 3. Generate the application key

Laravel needs a unique encryption key before it will run. Without this you'll get a `MissingAppKeyException`.

```bash
php artisan key:generate
```

This fills in the `APP_KEY` value in your `.env` file automatically.

### 4. Create the SQLite database file

The `.env` file is configured with `DB_CONNECTION=sqlite`, which points at `database/database.sqlite`. This file isn't committed to the repo, so you need to create it yourself — otherwise you'll get a "Database file ... does not exist" error.

**macOS/Linux:**
```bash
touch database/database.sqlite
```

**Windows (PowerShell):**
```powershell
New-Item database\database.sqlite -ItemType File
```

### 5. Run migrations

```bash
php artisan migrate
```

This creates the required tables (including `sessions`, `cache`, and `jobs`, which the app needs since `.env` sets `SESSION_DRIVER=database` and `QUEUE_CONNECTION=database`).

### 6. Build frontend assets

```bash
npm run dev
```

(or `npm run build` for a production build)

### 7. Serve the application

```bash
php artisan serve
```

Then visit the URL shown in your terminal (usually `http://127.0.0.1:8000`).

## Troubleshooting

| Error | Cause | Fix |
|---|---|---|
| `MissingAppKeyException` | `.env` has no `APP_KEY` set | Run `php artisan key:generate` |
| `Database file at path ... does not exist` | `database/database.sqlite` hasn't been created | Create the file (see step 4), then run `php artisan migrate` |
| Generic 500 error, no details | `APP_DEBUG=false` in `.env` hides the real error | Temporarily set `APP_DEBUG=true` in `.env` and check `storage/logs/laravel.log` |
| 500 error with no other symptoms | `storage/` or `bootstrap/cache/` not writable | Ensure those folders have write permissions |
