# MBG Traceability on Supabase (free, no card, no server)

This branch replaces the Laravel backend with **Supabase** (PostgreSQL + its
auto-generated API). The React frontend talks straight to Supabase, so there's
**no backend to host** — the whole thing is:

```
GitHub Pages  ─►  React frontend (static)
                     │  @supabase/supabase-js  (PostgREST + RPC)
                     ▼
Supabase      ─►  PostgreSQL: tables, triggers, views, traceability functions
```

The page components are unchanged — [src/api/axiosConfig.ts](frontend/src/api/axiosConfig.ts)
is now a thin shim that turns the old `api.get/post/...` calls into Supabase queries.

> Note: `laporan_keracunan` (was MongoDB) becomes a Postgres table with a
> **`jsonb`** column for `riwayat_audit` — still document-style data, now inside SQL.

---

## 1. Create a Supabase project (free, no card)
1. Go to **[supabase.com](https://supabase.com)** → sign in with GitHub.
2. **New project** → name `mbg-trace`, set a database password (save it), region
   **Southeast Asia (Singapore)**. Wait ~2 min for it to provision.

## 2. Load the schema + data
1. Left sidebar → **SQL Editor** → **New query**.
2. Paste the entire contents of **[supabase/schema.sql](supabase/schema.sql)** → **Run**.
3. It creates all tables, seeds the data, adds the triggers/views, the
   `trace_from_report` / `trace_from_supplier` functions, and RLS policies.
   You should see "Success".

## 3. Get your API credentials
**Settings → API**:
- **Project URL** → `https://xxxxxxxx.supabase.co`
- **Project API keys → `anon` `public`** → a long `eyJ...` key (safe for the browser)

## 4. Point the frontend at your project
Put both values in **[frontend/.env.production](frontend/.env.production)**:
```
VITE_SUPABASE_URL=https://xxxxxxxx.supabase.co
VITE_SUPABASE_ANON_KEY=eyJ...your-anon-key...
```
For **local dev** (`npm run dev`), also create **`frontend/.env.local`** with the same two lines.

## 5. Run it
- **Locally:** `cd frontend && npm install && npm run dev` → open the printed URL.
- **Live on GitHub Pages:** commit the filled-in `.env.production`, merge this
  branch to `main`, and the existing [Pages workflow](.github/workflows/deploy-pages.yml)
  builds & deploys. Enable **Settings → Pages → Source = GitHub Actions**.
  Site: `https://saktisadhana.github.io/mbg-trace/`

That's it — fully free, no card, no server, wakes instantly (Supabase is always-on free tier).

---

## Notes
- **RLS:** the schema enables Row-Level Security with a permissive `demo_all`
  policy (anon can read/write) because the app has no real auth. For anything
  beyond a demo, tighten these policies.
- **Traceability:** the Postgres functions return JSON matching the old Laravel
  shape, so the Traceability page works unchanged via `supabase.rpc(...)`.
- The old Laravel/MySQL/Mongo stack still lives on `main` if you ever want it.
