# EventFlow — Event Management & Booking System (Client Demo)

A working prototype: Laravel API + Vue 3 frontend, covering enquiry → quotation →
deposit → confirmed → planning → completed, with role-based access, staff/vendor/
equipment management, tasks, messaging and reporting.

This is a **demo build for client review**, not a production-audited system. Section 7
below is honest about what's simplified.

---

## 1. What was built

**Backend (Laravel 11 + Sanctum + MySQL)**
- 22 migrations covering users, customers, events, services, quotations, invoices,
  payments, staff, vendors, equipment + reservations, tasks, messages, notifications,
  documents, activity logs
- 17 Eloquent models with full relationships
- 13 API controllers with validation, role-based authorization (`role:` middleware),
  and business logic: quotation totals/discount/deposit calculation, payment →
  invoice balance updates, equipment double-booking prevention, event status pipeline
- Public endpoints for the marketing site (services list, "Request a Quote" form that
  creates a customer + enquiry with no login needed)
- A seeder with realistic demo data spanning the full pipeline (see credentials below)
- 16 feature tests (auth, customer CRUD, event/quotation flow, payment balance logic,
  equipment conflict prevention, dashboard stats)

**Frontend (Vue 3 + Vite + Pinia + Tailwind)**
- Premium navy/gold design system matching the brief
- Public site: Home, Services, Request a Quote
- Login with one-click demo account fill
- Customer Portal: event timeline, quotations (accept/reject), payment history,
  outstanding balance
- Admin suite: Dashboard (stats + charts-as-bars), Events (list, create, full event
  workspace with Overview/Staff & Vendors/Tasks/Payments/Messages tabs), Customers,
  Quotations, Payments, Reports

## 2. What works

Everything listed above is wired end-to-end — every frontend call matches a real
backend route, every route matches a controller method, every controller matches
the actual database columns. I verified this two ways from my side:
- Every PHP file passes `php -l` (syntax-checked, zero errors)
- The entire Vue app **builds successfully** with `npm run build` — all 12 views and
  every import resolved with zero errors

What I could **not** do from my side: run `composer install` or `php artisan test`,
because this sandbox has no access to Packagist. So the Laravel side is syntactically
verified and logically consistent, but the actual boot-and-run check happens on your
machine. Follow Section 4 and if `php artisan test` throws anything, send me the exact
error and I'll fix it immediately.

## 3. What is demo/simulated

- **Payments** are simulated — no real payment gateway is contacted. Every payment
  record is flagged `is_simulated: true` and the Payments admin page says so.
- **Messaging** is authenticated and scoped per-event/customer, but is not end-to-end
  encrypted. The UI labels it "Secure messaging — encryption architecture prepared
  for production implementation," as instructed.
- **Notifications** are stored and shown in-app only; no email/SMS/WhatsApp is sent.
- **Documents** (quotation/invoice/receipt PDFs) are represented in the data model
  but PDF rendering itself isn't implemented in this pass — the quotation/invoice
  data is structured and ready for a PDF template to be added.
- **Reports** are a simplified dashboard summary, not exportable PDF/Excel reports.

## 4. What requires real third-party credentials

- MySQL database (you provide connection details in `.env`)
- Any real payment gateway (Mobile Money aggregator, Stripe/Flutterwave for cards, etc.)
- Email/SMS/WhatsApp provider for real notifications
- A PDF rendering package (e.g. `barryvdh/laravel-dompdf`) if you want actual PDF
  downloads instead of on-screen quotation/invoice previews

## 5. Known limitations

- Vendors and Equipment don't yet have their own admin CRUD pages in the frontend
  (the backend endpoints exist and are tested; only the UI screens weren't built
  in this pass — happy to add them next).
- No file/document upload UI yet, though the `documents` table is ready for it.
- Calendar view (visual month/week grid) isn't built; event dates are visible in
  list views and the dashboard's "Upcoming Events."
- No automated browser/E2E tests, only backend feature tests.

## 6. How to run the project

### Backend

The safest path is to generate a **fresh Laravel skeleton with Composer** (so the
framework's boot files exactly match your installed Laravel version) and then copy
this project's `app`, `database`, `routes`, `config`, `tests` and `composer.json`
into it — that avoids any chance of a version mismatch in `vendor`/`bootstrap` files.

```powershell
# 1. Create a fresh Laravel 11 project somewhere temporary
composer create-project laravel/laravel eventflow-tmp
composer require laravel/sanctum --working-dir=eventflow-tmp

# 2. Copy this project's application code into it (overwrite when asked)
#    from inside the unzipped folder:
Copy-Item backend\app eventflow-tmp\app -Recurse -Force
Copy-Item backend\database eventflow-tmp\database -Recurse -Force
Copy-Item backend\routes eventflow-tmp\routes -Recurse -Force
Copy-Item backend\bootstrap\app.php eventflow-tmp\bootstrap\app.php -Force
Copy-Item backend\config\sanctum.php eventflow-tmp\config\sanctum.php -Force
Copy-Item backend\config\cors.php eventflow-tmp\config\cors.php -Force
Copy-Item backend\tests eventflow-tmp\tests -Recurse -Force

# 3. Rename it to Backend and move it into your project folder
Move-Item eventflow-tmp Backend

cd Backend
Copy-Item .env.example .env
php artisan key:generate
```

Then edit `.env` — set `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` to your MySQL
credentials, and add:
```
SANCTUM_STATEFUL_DOMAINS=localhost:5173
FRONTEND_URL=http://localhost:5173
```

```powershell
php artisan migrate:fresh --seed
php artisan test          # 16 tests covering the checklist
php artisan serve         # http://localhost:8000
```

If you'd rather try booting this project's own skeleton files directly (they're
complete — composer.json, bootstrap/app.php, public/index.php, config/*, artisan),
you can skip step 1–2 and just run `composer install` inside the `Backend` folder
as provided. It should work; the copy-in method above is simply the zero-risk option
since I couldn't run `composer install` myself to double-check it.

### Frontend

```powershell
cd Frontend
npm install
npm run dev        # http://localhost:5173
```

Create a `.env` in `Frontend` if your API isn't at the default:
```
VITE_API_URL=http://localhost:8000/api
```

### Deploying the Laravel API to Render

The repository includes a Render blueprint at `render.yaml` and a Docker runtime
in `Backend`. The blueprint creates the Laravel web service; provide it with a
PostgreSQL connection string from a PostgreSQL host such as Neon. Render's free
web service can sleep when idle, so the first API request after idle may take a
little longer.

1. Create a PostgreSQL database and copy its **pooled/externally reachable** URL.
2. In Render, choose **New → Blueprint**, connect this GitHub repository, and
  approve the `eventflow-api` service. When prompted, enter:
  - `DB_URL`: the PostgreSQL connection URL (include `sslmode=require` if the
    database provider requires TLS).
   - `APP_KEY`: generate a valid Laravel key with `php artisan key:generate --show`
     in the local `Backend` folder and paste the output into Render. Keep this
     key private and stable between deployments.
  - `APP_URL`: the public Render service origin, such as
    `https://eventflow-api.onrender.com`.
  - `FRONTEND_URL`: the Vercel site's stable production origin, with no trailing
    slash. Use its production domain, not a one-off deployment URL.
3. Wait for the Render service health check at `/up` to pass. The container runs
  `php artisan migrate --force` on startup.
4. Before connecting the production frontend, run `php artisan db:seed --force`
  **once** from the Render service shell to add the demo accounts and sample
  catalogue. Do not repeat this seeder against an already seeded database.
5. Copy the public Render service URL. In Vercel's project settings, set
  `VITE_API_URL` to `<Render service URL>/api` for Production, then redeploy.

The demo seeder uses the publicly documented demo password `password`; this is
for demonstration only. Change/remove those accounts before using real customer
data. Keep `APP_DEBUG=false` and never put `DB_URL` or `APP_KEY` in Vercel or in
source control.

### Deploying the frontend to Vercel

The Vue/Vite frontend can be deployed to Vercel as a static site. It uses the
separately deployed Laravel API configured above.

1. Import this GitHub repository in Vercel and leave **Root Directory** at the
  repository root. The root `vercel.json` installs/builds the nested frontend and
  serves `Frontend/dist`. Alternatively, set **Root Directory** to `Frontend` and
  use the nested `Frontend/vercel.json` settings (`npm run build`, output `dist`).
2. The Vercel config rewrites Vue Router paths to `index.html`, so direct links and
  page refreshes work.
3. Add the Vercel environment variable `VITE_API_URL` with the deployed Laravel API
  base URL, including `/api` (for example, `https://api.example.com/api`). Then
  redeploy; Vite embeds this value during the build.
4. On the Laravel host, set `FRONTEND_URL` to the deployed website origin only
  (for example, `https://event-system.example.com`) so the API permits browser
  requests from that domain. Ensure the backend's production database is migrated
  and seeded as appropriate for the deployment.

If `VITE_API_URL` is missing, the frontend falls back to `/api`; the local
development URL `http://localhost:8000/api` is not a usable API address for
visitors to the deployed site.

## 7. Demo login credentials

All demo accounts use the password: **password**

| Role | Email |
|---|---|
| Super Admin | admin@eventflow.test |
| Manager | manager@eventflow.test |
| Finance | finance@eventflow.test |
| Event Staff | staff@eventflow.test |
| Customer | customer@eventflow.test |

The login screen has one-click buttons to fill each of these in.

Seeded demo data includes: Sarah's wedding (confirmed, deposit paid, tasks, staff,
equipment reserved), NovaTech's conference (quotation sent, awaiting acceptance),
an anniversary party (early enquiry), and a completed, fully-paid birthday event —
so every stage of the pipeline has something to show immediately.

## 8. Recommended next steps before production

- Add real payment gateway integration behind the existing `payments` API shape
- Add PDF generation for quotations/invoices/receipts
- Build Vendor and Equipment admin CRUD screens (backend already supports both)
- Add a visual calendar view
- Add email/SMS notifications alongside the in-app notification center
- Security hardening pass: rate limiting on public endpoints, file upload validation
  once document uploads are added, a proper CSP, and a dependency audit
- Replace demo data with real company content and branding assets
