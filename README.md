# ATHAR (أَثَر) — Literary Quote Platform

ATHAR is an Arabic-first bilingual quotation platform built with Laravel 13, PHP 8.3+, Blade, Tailwind CSS 4, Vite, and PostgreSQL in production (SQLite locally). Visitors can explore quotations and authors, search and filter the public feed, copy quotations, view the daily quotation, and enter Zen reading mode. Registered members can publish quick or book quotations, like and save content, manage their own posts, and update their profile avatar.

## Features

- Arabic and English interface with RTL/LTR layout switching.
- Persistent light and dark themes.
- Public quote feed with search, type filters, sorting, and pagination.
- Quick quotations and book quotations with required book cover and attribution.
- Normalized Authors and Books catalog records while preserving existing quote metadata.
- Authors index and individual author pages.
- Stable Quote of the Day, persisted by Riyadh calendar date.
- Zen reading mode with previous/next navigation, arrow keys, and Escape to exit.
- Authenticated likes and saved quotations with database uniqueness constraints.
- Profile avatar management, account statistics, and owner-only quote management.
- Four high-resolution PNG sharing templates and browser-native sharing where supported.
- Docker, Nginx, Render Blueprint, and Neon PostgreSQL deployment configuration.

## Requirements

- PHP 8.3 or newer with `pdo_sqlite`, `pdo_pgsql`, `gd`, `fileinfo`, `mbstring`, `xml`, `curl`, `openssl`, and `zip` extensions as appropriate to the environment.
- Composer 2.x.
- Node.js 22 LTS and npm 10+.

## Local setup — Windows PowerShell

Run from the project root (`athar-quote-platform-main`):

```powershell
Copy-Item .env.example .env
New-Item -ItemType File -Force database/database.sqlite | Out-Null
composer install
php artisan key:generate
php artisan migrate
php artisan storage:link
npm ci
npm run build
```

To add the sample local content, run this only in your local development database:

```powershell
php artisan db:seed
```

The demo seeder creates sample local accounts. Do not run it against the production database. For a local session, keep the credentials shown in `database/seeders/DatabaseSeeder.php` private and change them before sharing a development instance.

Start the application in two PowerShell terminals:

```powershell
php artisan serve
```

```powershell
npm run dev
```

Open `http://127.0.0.1:8000`.

## Tests and build

```powershell
php artisan test
npm run build
```

The project uses SQLite `:memory:` for automated tests when configured by `phpunit.xml`; production migrations are additive and do not reset existing records.

## Deployment

See [`docs/DEPLOYMENT_RENDER_NEON.md`](docs/DEPLOYMENT_RENDER_NEON.md) for the complete GitHub → Neon → Render Docker deployment walkthrough and the required environment variables.

Important deployment notes:

- Never commit `.env`, database passwords, or generated application keys.
- Render's default filesystem is ephemeral. Production-uploaded book covers and avatars are stored as data URIs in PostgreSQL by this application, so user uploads do not depend on the container filesystem. Seeded sample covers are packaged under `public/images/books/`.
- The Render startup script runs `php artisan migrate --force` and does **not** run the demo seeder automatically.
- The `DATABASE_URL` secret must be a Neon PostgreSQL connection string with SSL required. The app can use Neon's pooled `-pooler` endpoint for web requests; the startup script switches to the direct endpoint for migrations before restoring the pooled endpoint for runtime.
- Set a valid Laravel `APP_KEY` in Render. Generate it locally with `php artisan key:generate --show` and paste it only into Render's Environment settings; never commit it.

## Main project paths

- `app/Http/Controllers/` — HTTP controllers.
- `app/Models/` — User, Quote, Author, Book, like, and saved-quote models.
- `app/Services/QuoteOfTheDayService.php` — deterministic daily quote selection.
- `database/migrations/` — schema and non-destructive catalog migrations.
- `resources/views/` — Blade pages and reusable components.
- `resources/js/share-generator.js` — high-resolution PNG share-card generator.
- `docs/SRS.md` and `docs/ERD.md` — product requirements and database relationships.

## Deployment verification

After deployment, verify:

1. `https://<your-render-service>.onrender.com/health` returns JSON with `status: ok`.
2. `https://<your-render-service>.onrender.com/health?db=true` returns JSON with `database: ok`.
3. `/`, `/authors`, `/daily`, and `/zen` render correctly.
4. Registration, login, quote publishing, likes, saves, and avatar uploads work.
5. Render logs show migrations completing without errors.
