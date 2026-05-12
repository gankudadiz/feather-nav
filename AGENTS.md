# Repository Guidelines

## Project Structure & Module Organization
This repository is a Flight PHP-based personal navigation site.

- `app/Controllers/`: request handlers for auth, admin, links, settings, and dashboard flows
- `app/Helpers/`, `app/Middleware/`: shared utilities, CSRF/auth/session helpers, and asset loading
- `app/routes.php`: route registration and auth guards
- `config/`: app and database bootstrap
- `database/init.sql`: schema bootstrap for first-time setup
- `public/`: web entrypoint and built frontend assets
- `resources/`: PHP views plus Vite-managed CSS/JS sources
- `scripts/setup_db.php`: local database bootstrap script

## Build, Test, and Development Commands
- `composer install`: install PHP dependencies
- `npm install`: install frontend dependencies
- `php scripts/setup_db.php`: create the configured database schema and seed the admin account
- `npm run dev`: start the Vite dev server on `127.0.0.1:5173`
- `php -S 127.0.0.1:8100 -t public`: start the PHP app locally
- `npm run build`: generate production assets into `public/assets`

## WSL2 Execution Policy
This repository's personal-development baseline is WSL2 native only.

- Execute project commands from `/home/wanglizhou/web/personal_navigation`.
- Use only WSL-native `php`, `composer`, `node`, `npm`, `git`, and `mysql`.
- Do not call Windows executables or runtime paths under `/mnt/c` or `/mnt/d`.
- Local app URL is `http://127.0.0.1:8100`.
- MySQL is `127.0.0.1:3307` with the locally configured root account.
- Long-running processes still require explicit user intent. Do not proactively start `php -S`, `npm run dev`, watchers, or similar persistent processes unless the user clearly asks for them.
- If the database or runtime is not ready, stop and report the missing prerequisite instead of guessing.

## Coding Style & Naming Conventions
Use strict types, keep PHP classes in `StudlyCase`, methods in `camelCase`, and preserve the existing lightweight MVC split. Follow nearby files for formatting and prefer concise Chinese comments only where the code flow is not obvious.
