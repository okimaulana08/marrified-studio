# Deploy Workflow

Marrified Studio pakai 2-branch flow: `development` untuk staging server, `main` untuk production. Push langsung ke `main` di-block via Branch Protection — semua perubahan masuk via Pull Request dari `development`.

## Branch Flow

```
feature/* ──merge──▶ development ──PR──▶ main
                         │                 │
                    deploy DEV         deploy PROD
                    (auto on push)     (auto on merge)
```

- Daily dev work: branch dari `development`, commit, merge balik ke `development` → CI auto-deploy ke dev server.
- Release ke prod: buka PR dari `development` ke `main`, review, merge → CI auto-deploy ke prod server.

## URL & Path

| Env  | URL                                                | Server path (parent of `public/`)                                        |
|------|----------------------------------------------------|--------------------------------------------------------------------------|
| Dev  | https://codie.my.id/marrivitation                  | `/home/u778058510/domains/codie.my.id/public_html/marrivitation`         |
| Prod | (TBD)                                              | (TBD)                                                                    |

## GitHub Secrets (Settings → Secrets and variables → Actions)

Wajib di-set sebelum workflow pertama jalan:

| Secret              | Isi                                                                                |
|---------------------|------------------------------------------------------------------------------------|
| `SSH_HOST`          | IP atau hostname Hostinger SSH (lihat hPanel → Advanced → SSH Access)              |
| `SSH_PORT`          | Port SSH Hostinger (biasanya `65002` untuk shared)                                 |
| `SSH_USERNAME`      | Username SSH (mis. `u778058510`)                                                   |
| `SSH_PRIVATE_KEY`   | Isi private key (`-----BEGIN OPENSSH PRIVATE KEY-----…`) — generate ed25519 di lokal, public key paste ke Hostinger SSH Access |
| `DEV_DEPLOY_PATH`   | `/home/u778058510/domains/codie.my.id/public_html/marrivitation`                   |
| `PROD_DEPLOY_PATH`  | path Laravel app di server prod (parent dari `public/`)                            |
| `TELEGRAM_BOT_TOKEN`| (optional) untuk notifikasi deploy                                                 |
| `TELEGRAM_CHAT_ID`  | (optional) chat ID grup/personal                                                   |

## One-time Server Setup (per env)

SSH ke server, lakukan sekali per environment sebelum deploy pertama:

```bash
# 1. Pastikan folder ada
mkdir -p /home/u778058510/domains/codie.my.id/public_html/marrivitation
cd /home/u778058510/domains/codie.my.id/public_html/marrivitation

# 2. Buat MySQL database via Hostinger hPanel
#    → Databases → MySQL Databases → New Database
#    Catat: nama DB, username, password yang digenerate

# 3. Buat .env (copy dari .env.example lokal, sesuaikan)
nano .env
# Set:
#   APP_ENV=staging              # atau production untuk prod
#   APP_DEBUG=false
#   APP_URL=https://codie.my.id/marrivitation
#   APP_KEY=...                  # generate via: php artisan key:generate --show
#   DB_CONNECTION=mysql
#   DB_HOST=127.0.0.1            # atau host MySQL dari hPanel (biasanya localhost)
#   DB_PORT=3306
#   DB_DATABASE=u778058510_marrified
#   DB_USERNAME=u778058510_admin
#   DB_PASSWORD=...

# 4. Set PHP version → 8.3 via hPanel → Websites → Advanced → PHP Configuration
#    Pilih PHP 8.3 untuk subdomain/folder marrivitation

# 5. Permissions
chmod -R 775 storage bootstrap/cache
chown -R u778058510:u778058510 .

# 6. .htaccess di public/ — Laravel default sudah ada setelah rsync,
#    tapi pastikan AllowOverride All di-enable Hostinger (default ya).

# 7. Pointing document root ke ./public via .htaccess (kalau URL pakai
#    subfolder seperti codie.my.id/marrivitation, bukan subdomain)
```

## Branch Protection (one-time, via GitHub UI)

GitHub repo → Settings → Branches → Add branch protection rule:

- Branch name pattern: `main`
- ✅ Require a pull request before merging
- ✅ Require approvals: 1 (atau 0 kalau solo dev)
- ✅ Require status checks to pass before merging
  - Status check: `Build & Deploy → development` (biar dipastikan dev sudah deploy sukses)
- ✅ Do not allow bypassing the above settings
- ❌ Allow force pushes — keep off

Hasilnya: `git push origin main` direct akan di-reject; semua merge wajib via PR.

## Daily Workflow (developer side)

```bash
# Mulai feature baru
git checkout development
git pull
git checkout -b feat/section-templates

# Code, commit, push
git add -A
git commit -m "feat: ..."
git push -u origin feat/section-templates

# Buka PR feat/section-templates → development di GitHub
# Setelah merge: CI auto-deploy ke dev server, cek di codie.my.id/marrivitation

# Ready untuk prod release:
git checkout development
git pull
# Buka PR development → main di GitHub
# Setelah review + merge: CI auto-deploy ke prod
```

## Troubleshooting

**Deploy gagal di rsync step**
- Cek `SSH_HOST`/`SSH_PORT` benar (Hostinger shared default port `65002`)
- Cek private key valid: `ssh -i ~/.ssh/key u778058510@host -p 65002`
- Pastikan public key sudah di-add di Hostinger hPanel → Advanced → SSH Access

**Post-deploy artisan command error**
- SSH manual ke server, jalankan command bermasalah → lihat error
- Kalau PHP version mismatch: hPanel → PHP Configuration → set ke 8.3

**500 setelah deploy**
- Cek `storage/logs/laravel.log` di server
- Common: storage permission (`chmod -R 775 storage bootstrap/cache`)
- Common: missing `APP_KEY` di `.env`

**Asset 404 (CSS/JS)**
- Pastikan `npm run build` di CI sukses → `public/build/` ada di-rsync
- Cek `.env` `APP_URL` matches URL aktual (untuk URL absolut Vite)
