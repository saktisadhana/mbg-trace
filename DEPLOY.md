# Deploying MBG Traceability (free stack)

Architecture — all free, no credit card:

```
GitHub Pages  ─►  React frontend (static)
                     │  HTTPS → VITE_API_URL
                     ▼
Render        ─►  Laravel API  (Dockerfile, PHP 8.2 + ext-mongodb)
                     ├─► Aiven MySQL   (sppg_db, TLS required)
                     └─► MongoDB Atlas (mbg_nosql.laporan_keracunan)
```

The Laravel cross-DB SQL↔NoSQL code is unchanged — only hosting + env config.

> Trade-off: Render's free service **spins down after ~15 min idle**, so the first
> request after a nap takes ~50s, then it's fast again. Fine for a demo.

> The old Fly.io files (`fly.toml`, `deploy/mysql/`) are kept as a paid alternative.

---

## 1. MongoDB Atlas (NoSQL side) — free M0

1. Create a **free M0 cluster** (region near Singapore).
2. **Database Access** → add a user (e.g. `mbg` / strong password).
3. **Network Access** → allow `0.0.0.0/0` (Render egress IPs vary).
4. Copy the **SRV connection string**:
   `mongodb+srv://mbg:<pw>@cluster0.xxxx.mongodb.net/?retryWrites=true&w=majority`
5. Seed the collection (you have `mongosh`):
   ```powershell
   $docs = Get-Content -Raw db/laporan_keracunan.json
   mongosh "<SRV_URI>/mbg_nosql" --eval "db.laporan_keracunan.insertMany($docs)"
   ```

---

## 2. Aiven MySQL (SQL side) — free plan

1. Create an Aiven account → **Create service → MySQL → Free plan** (region near
   Singapore). Wait until it's *Running*.
2. From the service **Overview → Connection information**, note: **Host, Port,
   User** (`avnadmin`), **Password**, and download the **CA certificate** (`ca.pem`).
3. Save that cert in the repo as **`certs/aiven-ca.pem`** and commit it
   (a CA cert is public, not a secret) — see [certs/README.md](certs/README.md).
4. **Import the schema** (run in **Git Bash** so `<` redirection works):
   ```bash
   "C:/xampp/mysql/bin/mysql.exe" --ssl-ca=certs/aiven-ca.pem \
       -h <AIVEN_HOST> -P <PORT> -u avnadmin -p \
       < db/sppg_database_lengkap.unix.sql
   ```
   The script runs `CREATE DATABASE sppg_db` itself, so nothing else is needed.

---

## 3. Render (Laravel API)

1. Push this repo to GitHub (already done).
2. Render dashboard → **New + → Blueprint** → pick `saktisadhana/mbg-trace`.
   Render reads [render.yaml](render.yaml) and creates the `mbg-trace-api` service.
3. When prompted, set the **secret** env vars:

   | Key | Value |
   |-----|-------|
   | `APP_KEY` | generate one: `C:\xampp\php\php.exe artisan key:generate --show` → paste the `base64:...` output (do **not** commit it) |
   | `DB_HOST` | Aiven host |
   | `DB_PORT` | Aiven port |
   | `DB_USERNAME` | `avnadmin` |
   | `DB_PASSWORD` | Aiven password |
   | `MONGODB_URI` | your Atlas SRV string |

   (`DB_DATABASE=sppg_db`, `MYSQL_ATTR_SSL_CA`, CORS, etc. are already in the blueprint.)
4. Render builds the Dockerfile and deploys. Your API URL will be
   `https://mbg-trace-api.onrender.com`.

**Smoke test** (first call may take ~50s if the service was asleep):
```bash
curl https://mbg-trace-api.onrender.com/api/suppliers          # MySQL path
curl https://mbg-trace-api.onrender.com/api/laporan-keracunan  # Mongo + cross-DB join
```

> If your Render service name isn't `mbg-trace-api`, update `VITE_API_URL` in
> [frontend/.env.production](frontend/.env.production) and the origin in
> [config/cors.php](config/cors.php) stays the same (it's the *frontend* origin).

---

## 4. Frontend on GitHub Pages

1. GitHub repo → **Settings → Pages → Build and deployment → Source = GitHub Actions**.
2. [frontend/.env.production](frontend/.env.production) already points at the Render URL.
3. Push to `main` → the [workflow](.github/workflows/deploy-pages.yml) builds & deploys.
4. Live at **https://saktisadhana.github.io/mbg-trace/**

---

## Notes & gotchas

- **Cold starts** — Render free spins down when idle; first hit ~50s. Upgrade the
  Render plan (paid) to keep it always-on if needed.
- **ext-mongodb** is compiled into the image via `pecl install mongodb` in the
  [Dockerfile](Dockerfile) — replaces your local hand-installed `php_mongodb.dll`.
- **Aiven TLS** — both the running app and the import use `certs/aiven-ca.pem`.
- **Local dev is unchanged** — `VITE_API_URL` is empty locally, so Vite proxies
  `/api` → `localhost:8000` exactly as before.
```
