# ATHAR — GitHub, Neon, and Render Deployment Guide

This guide deploys the Laravel app as a Docker Web Service on Render and uses Neon PostgreSQL for persistent application data. Complete each section in order. Never commit `.env`, `APP_KEY`, or the Neon connection string.

## 1. Prepare the project locally

From Windows PowerShell, in the project root:

```powershell
php -v
composer --version
node -v
npm -v
php -m | Select-String "pdo_sqlite|pdo_pgsql|gd|fileinfo|mbstring|xml|curl"
composer install
Copy-Item .env.example .env -ErrorAction SilentlyContinue
php artisan key:generate
New-Item -ItemType File -Force database/database.sqlite | Out-Null
php artisan migrate
php artisan storage:link
npm ci
npm run build
php artisan test
```

`php artisan db:seed` is optional and is intended for a local development database only. Do not seed the production database; the sample seeder includes demo accounts for local testing.

Before pushing, confirm that `.env` is ignored:

```powershell
git status --short
```

The `.env` file must not appear in the output as an untracked or staged file.

## 2. Create the GitHub repository and push

1. Sign in to GitHub and create a new empty repository named `athar` (do not add a README, license, or `.gitignore`, because this project already includes them).
2. In PowerShell, from the project root:

```powershell
git init
git branch -M main
git add .
git status --short
git commit -m "Prepare ATHAR for deployment"
git remote add origin https://github.com/<YOUR_GITHUB_USERNAME>/athar.git
git push -u origin main
```

Replace `<YOUR_GITHUB_USERNAME>` with your actual GitHub username. Check the staged file list before committing. Never add `.env`, passwords, API keys, or database credentials.

## 3. Create the Neon PostgreSQL database

1. Create or open a Neon project in the Neon Console.
2. Choose a region close to the Render service region.
3. Open **Connect** / **Connection Details**.
4. Select the database and role you will use for ATHAR.
5. Enable the **Pooled connection** option and copy the PostgreSQL connection string.
6. Confirm that the string includes `sslmode=require`. Keep the value private.

The pooled hostname normally contains `-pooler`. ATHAR uses that endpoint for normal web requests. `docker/start.sh` temporarily changes the host to the direct endpoint while it runs Laravel migrations, then restores the pooled URL before it creates the production configuration cache. This avoids caching the temporary migration endpoint for web traffic.

## 4. Create the Render Web Service

1. Sign in to Render.
2. Choose **New → Blueprint** (recommended because this repository contains `render.yaml`).
3. Connect the GitHub account and select the `athar` repository.
4. Select the branch `main` and apply the Blueprint.
5. When Render asks for secret environment values, provide:
   - `APP_KEY`: generate locally with `php artisan key:generate --show`; paste the full output beginning with `base64:`.
   - `DATABASE_URL`: paste the Neon pooled PostgreSQL connection string. Do not put the value in `render.yaml` or GitHub.
6. Choose the service region closest to your Neon project.
7. Start the first deployment and monitor the **Events** and **Logs** tabs.

If the service already exists and you are updating its Blueprint, Render does not re-prompt for variables declared with `sync: false`. In that case, add or update `APP_KEY` and `DATABASE_URL` manually in **Render → athar → Environment**, then save and deploy.

## 5. Environment variables expected by Render

The checked-in `render.yaml` configures non-secret settings. The following secret values must be configured in the Render Dashboard:

| Key | Value |
|---|---|
| `APP_KEY` | Laravel key from `php artisan key:generate --show` |
| `DATABASE_URL` | Neon PostgreSQL pooled connection string with `sslmode=require` |

Render also supplies `PORT` and `RENDER_EXTERNAL_URL` at runtime. Laravel uses `RENDER_EXTERNAL_URL` as the default `APP_URL` if `APP_URL` is not manually defined.

The app sets production mode, disables debug output, uses Asia/Riyadh time, Arabic as the default interface language, cookie sessions, a file cache, and PostgreSQL. Do not set `DB_CONNECTION=sqlite` on Render.

## 6. Verify the first deployment

After Render reports a successful deploy, open the service URL and test:

```text
https://<YOUR_RENDER_SERVICE>.onrender.com/health
https://<YOUR_RENDER_SERVICE>.onrender.com/health?db=true
```

Expected responses include:

- `/health`: JSON with `"status":"ok"`.
- `/health?db=true`: JSON with `"database":"ok"`.

Then check these pages in a browser:

- `/` — public quote feed and Quote of the Day.
- `/authors` — author directory.
- `/daily` — stable daily quotation.
- `/zen` — distraction-free reading.
- `/login` and `/register` — authentication.

Create a test account through the website, publish a quick quote, publish a book quote with a valid cover image, like and save a quote, and verify the My Quotes and Account pages.

## 7. Images and file persistence

Render services have an ephemeral filesystem by default. ATHAR stores user-uploaded book covers and profile avatars as validated image data URIs in PostgreSQL when `APP_ENV=production`; therefore those uploads survive a container restart or redeploy without relying on local container storage. The built-in sample covers live under `public/images/books/` and are included in the Docker image.

This choice keeps the first deployment self-contained, but it increases database storage and response payload size. For a larger public deployment, move uploaded images to a dedicated object-storage service (S3-compatible) and keep only the object keys in PostgreSQL.

## 8. Subsequent updates

After changing code locally:

```powershell
php artisan test
npm run build
git diff --check
git status --short
git add .
git commit -m "Describe the update"
git push
```

Render auto-deploys new commits from the connected `main` branch. Review the deploy logs, then repeat the health checks.

## 9. Important safety notes

- Never use `php artisan migrate:fresh`, `db:wipe`, or destructive database reset commands on production.
- The start script runs only additive Laravel migrations with `php artisan migrate --force`.
- The demo seeder is not executed automatically in production. Do not enable `RUN_SEEDER` for production.
- Keep `APP_DEBUG=false` in production.
- If `APP_KEY` is changed after users have started using the site, previously encrypted sessions/cookies and other encrypted Laravel data may become unreadable. Keep the original key safe.
- Free Render services are suitable for demos and experiments, not reliability-sensitive production use; they may sleep after inactivity and have resource limitations. Consider a paid instance for an always-on public service.
