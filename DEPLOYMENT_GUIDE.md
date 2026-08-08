# Go-Live Guide — Investment Intelligence Platform

This is an operational runbook, not one of the versioned architecture documents
(`00`-`14`). It explains how to actually boot the codebase in this zip and get
your first 3-5 users in, per `MVP_Launch_Strategy.docx` Phase 2.

## 0. What changed in this build

Every Module built before this pass (Project, Compliance, DataRoom, Document,
Investor, Notification, Analytics, AI) was source code only — there was no
`composer.json`, no `artisan`, no `bootstrap/`, and critically, **no login**.
This build adds:

- A minimal, real Laravel 11 skeleton so the existing code can boot.
- The Identity Module: real `users`/`user_roles`/`sessions` tables, JWT-based
  login (`POST /v1/auth/login`), and `jwt.auth` middleware now enforcing
  "must be logged in" on every other endpoint.
- `php artisan accounts:create` — the only way to create an account this
  Sprint (no public sign-up page yet).

**Not done in this build, on purpose (flagged, not hidden):**

- **No frontend/UI at all.** Every endpoint is JSON API only. Your 3-5 users
  need either a UI someone builds next, or you personally driving Postman/
  curl on a screen-share (the "Bonus" option in `MVP_Launch_Strategy.docx`).
- **AI features return "not configured."** `AiProviderGatewayInterface` is
  bound to a Null implementation — no OpenAI call is wired in yet, even
  though you mentioned an OpenAI key is ready. Wiring a real provider also
  requires registering a Model in the Platform Governance Model Registry
  (`10_PLATFORM_GOVERNANCE.md`), not just an adapter class — a separate task.
- **No self-registration.** Only you (via `accounts:create`) can make an
  account.
- **Fine-grained role checks** (e.g. "only a Compliance Officer may decide a
  review") are not retrofitted onto the 8 pre-existing Modules' routes yet —
  today, "logged in" is the only gate.
- This sandbox has no PHP/Composer, so none of this has been executed —
  only hand-verified for syntax, brace balance, and structural correctness.
  **Run it somewhere with PHP before you trust it.**

## 1. First run — do this locally first, not on Forge

```bash
cd aios-platform
composer install
cp .env.example .env
php -r "echo 'APP_KEY=base64:'.base64_encode(random_bytes(32)).PHP_EOL;" >> .env
php -r "echo 'JWT_SECRET='.bin2hex(random_bytes(32)).PHP_EOL;" >> .env

# Fastest option: SQLite, zero config
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
touch database/database.sqlite

php artisan migrate
php artisan accounts:create "Your Name" you@example.com admin
php artisan serve
```

If `php artisan serve` boots without errors and `accounts:create` printed a
password, the skeleton works. If it doesn't, fix that before deploying
anywhere — a broken local boot will be an identically broken Forge deploy.

Test the login:

```bash
curl -X POST http://127.0.0.1:8000/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"you@example.com","password":"<the printed password>"}'
```

You should get back `{"data":{"access_token": "...", ...}}`.

## 2. Creating your first 3-5 accounts

```bash
php artisan accounts:create "Ahmad bin Ismail" ahmad@example.com business_owner
php artisan accounts:create "Siti Aisha" siti@example.com investor
```

Roles: `business_owner`, `investor`, `admin`, `compliance_officer`. Each
command prints the email + generated password once — copy it immediately.
This is what you send as the "login link" in `Investor_Outreach_Scripts.docx`
— in practice, since there's no login page yet, share the email + password
and the API base URL, and personally run the requests for them (screen-share)
until a UI exists.

## 3. Deploying to Forge + Supabase

1. Supabase: Project Settings → Database → Connection string → copy host,
   port, database, username, password into `.env`'s `DB_*` values.
   `DB_SSLMODE=require` (already the default in `.env.example`).
2. Forge: create a new site pointed at your domain, PHP 8.2+, deploy this
   repo. Forge's deploy script should run:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   ```
3. Set the same `.env` values Forge's environment editor (`APP_KEY`,
   `JWT_SECRET`, `DB_*`, `OPENAI_API_KEY` if/when wired) — never commit real
   secrets to the repo (SEC-011).
4. Once deployed, repeat the `accounts:create` step over SSH on the Forge
   server (same command as above).

## 4. If you'd rather not deploy yet

Per `MVP_Launch_Strategy.docx`'s own "Bonus" note: running `php artisan serve`
locally and screen-sharing is a fully valid MVP for your first few users —
you don't need Forge live before your first real conversation with an
investor or project owner.
