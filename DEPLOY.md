# Deploying MBG Traceability

Architecture:

```
GitHub Pages  ─►  React frontend (static)
                     │  HTTPS → VITE_API_URL
                     ▼
Fly.io        ─►  Laravel API  (Dockerfile, PHP 8.2 + ext-mongodb)
                     ├─► Fly.io MySQL app   (sppg_db, private network)
                     └─► MongoDB Atlas      (mbg_nosql.laporan_keracunan)
```

The Laravel cross-DB SQL↔NoSQL logic is unchanged — only hosting + env config.

---

## 0. One-time tooling

```bash
# Fly CLI
curl -L https://fly.io/install.sh | sh      # or: iwr https://fly.io/install.ps1 -useb | iex  (PowerShell)
fly auth signup        # or: fly auth login
# GitHub CLI (optional) + a MongoDB Atlas account (atlas.mongodb.com)
```

---

## 1. MongoDB Atlas (NoSQL side)

1. Create a **free M0 cluster** (region near Singapore).
2. **Database Access** → add a user (e.g. `mbg` / strong password).
3. **Network Access** → allow `0.0.0.0/0` (Fly egress IPs vary).
4. Copy the **SRV connection string**:
   `mongodb+srv://mbg:<pw>@cluster0.xxxx.mongodb.net/?retryWrites=true&w=majority`
5. Seed the collection from the repo:
   ```bash
   mongoimport --uri "<SRV_URI>" --db mbg_nosql --collection laporan_keracunan \
     --file db/laporan_keracunan.json --jsonArray
   ```

---

## 2. MySQL on Fly (SQL side) — private app

```bash
fly apps create mbg-trace-mysql
fly volume create mysql_data -a mbg-trace-mysql -r sin -n 1 --size 1
fly secrets set -a mbg-trace-mysql \
    MYSQL_ROOT_PASSWORD=<root_pw> \
    MYSQL_DATABASE=sppg_db \
    MYSQL_USER=mbg \
    MYSQL_PASSWORD=<app_pw>
fly deploy -c deploy/mysql/fly.toml
```

**Import the schema** (the .sql has CRLF line endings that break `DELIMITER //`, so strip them first — same gotcha as local XAMPP):

```bash
# Terminal A — tunnel the private MySQL to localhost
fly proxy 3306 -a mbg-trace-mysql

# Terminal B — strip CR and import (use the XAMPP client or any mysql client)
sed 's/\r//' db/sppg_database_lengkap.sql > /tmp/schema.sql
"C:/xampp/mysql/bin/mysql.exe" -h 127.0.0.1 -P 3306 -u root -p sppg_db < /tmp/schema.sql
```

---

## 3. Laravel API on Fly

```bash
# Create the app WITHOUT generating a new fly.toml (we already have one)
fly apps create mbg-trace-api

# Reuse the APP_KEY from your local .env (or: php artisan key:generate --show)
fly secrets set -a mbg-trace-api \
    APP_KEY="base64:JZQuPrRJWphxG9avv6wYtK8oIiw+vFmChQWCZlnUMiU=" \
    DB_USERNAME=mbg \
    DB_PASSWORD=<app_pw> \
    MONGODB_URI="<SRV_URI>"

fly deploy
```

The non-secret env (DB_HOST=`mbg-trace-mysql.internal`, DB_DATABASE, MONGODB_DATABASE, etc.) is already in [fly.toml](fly.toml).

**Smoke test:**
```bash
curl https://mbg-trace-api.fly.dev/api/suppliers          # MySQL path
curl https://mbg-trace-api.fly.dev/api/laporan-keracunan  # Mongo + cross-DB join
```

If the API name differs from `mbg-trace-api`, update:
- [frontend/.env.production](frontend/.env.production) → `VITE_API_URL`
- `CORS_ALLOWED_ORIGINS` secret (below) stays the same (it's the *frontend* origin).

---

## 4. CORS (allow the Pages origin)

Default allowed origins are baked into [config/cors.php](config/cors.php)
(`https://saktisadhana.github.io` + localhost). To change without editing code:

```bash
fly secrets set -a mbg-trace-api \
    CORS_ALLOWED_ORIGINS="https://saktisadhana.github.io,http://localhost:5173"
```

---

## 5. Frontend on GitHub Pages

1. In the GitHub repo: **Settings → Pages → Build and deployment → Source = GitHub Actions**.
2. Make sure [frontend/.env.production](frontend/.env.production) points at your API.
3. Push to `main`. The [workflow](.github/workflows/deploy-pages.yml) builds and deploys automatically.
4. Site goes live at **https://saktisadhana.github.io/mbg-trace/**

> The Vite `base` is set to `/mbg-trace/` for production builds — if you rename the
> repo, update `base` in [frontend/vite.config.ts](frontend/vite.config.ts).

---

## Notes & gotchas

- **Cold starts:** the API scales to zero (`min_machines_running = 0`) and wakes on
  first request (~1–2s). Set it to `1` in [fly.toml](fly.toml) to keep it always-on.
- **MySQL is private** — no public port. Use `fly proxy` to reach it for imports/admin.
- **ext-mongodb** is compiled into the image via `pecl install mongodb` in the
  [Dockerfile](Dockerfile) — this replaces your hand-installed `php_mongodb.dll`.
- **Local dev is unchanged:** `VITE_API_URL` is empty locally, so Vite proxies
  `/api` → `localhost:8000` exactly as before.
```
