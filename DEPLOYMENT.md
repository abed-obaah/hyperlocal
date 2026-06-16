# Deployment — Hyperlocal API

Production runs on a single **AWS EC2 (Ubuntu)** instance with **Nginx + PHP-FPM + MySQL**, served over HTTPS at **https://hyperlocal-jerry.duckdns.org**. Deploys are automated with **GitHub Actions** (`appleboy/ssh-action`) on every push to `main`.

## Production facts
| | |
|---|---|
| Server | AWS EC2, Ubuntu |
| Web server | Nginx → PHP-FPM |
| Laravel root | `/var/www/hyperlocal` |
| Public path | `/var/www/hyperlocal/public` |
| Database | MySQL on the same instance (`hyperlocal`) |
| Domain | https://hyperlocal-jerry.duckdns.org |
| Repo | https://github.com/abed-obaah/hyperlocal.git (cloned at the Laravel root) |

---

## Auto-deploy (GitHub Actions)

`.github/workflows/deploy.yml` runs on every push to `main`. It SSHes into EC2 and runs:

```bash
cd /var/www/hyperlocal
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**Required GitHub secrets** (Repo → Settings → Secrets and variables → Actions):

| Secret | Value |
|--------|-------|
| `EC2_HOST` | EC2 public IP / `hyperlocal-jerry.duckdns.org` |
| `EC2_USER` | `ubuntu` |
| `EC2_SSH_KEY` | full contents of the `.pem` private key (the whole `-----BEGIN … END-----` block) |

To deploy: **push to `main`**, or re-run the latest job from the repo's **Actions** tab. Watch progress there; the SSH step prints `Deployment complete` on success.

> `migrate --force` only applies *new* migrations — it never reseeds, so live data is safe.
> If you set PHP `opcache.validate_timestamps=0` for performance, add `sudo systemctl reload php8.2-fpm` to the deploy script so new code isn't served from a stale opcache. With Ubuntu's default (`=1`) it isn't needed.

---

## One-time server setup (reference / disaster recovery)

Already done on the live box — kept here so the server can be rebuilt.

### 1. System packages
```bash
sudo apt-get update && sudo apt-get install -y nginx mysql-server git unzip \
  php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl \
  php8.2-bcmath php8.2-zip php8.2-gd php8.2-intl
curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
```
*(Adjust `php8.2` to your installed version — check with `ls /run/php/`.)*

### 2. Database
```bash
sudo mysql -e "CREATE DATABASE IF NOT EXISTS hyperlocal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'hyperlocal'@'localhost' IDENTIFIED BY 'YOUR_DB_PASSWORD';"
sudo mysql -e "GRANT ALL ON hyperlocal.* TO 'hyperlocal'@'localhost'; FLUSH PRIVILEGES;"
```

### 3. Clone the app at the Laravel root
```bash
sudo git clone https://github.com/abed-obaah/hyperlocal.git /var/www/hyperlocal
sudo chown -R ubuntu:ubuntu /var/www/hyperlocal
cd /var/www/hyperlocal
composer install --no-dev --optimize-autoloader
cp .env.example .env            # then edit (see below) and: php artisan key:generate
php artisan migrate --force --seed   # --seed only on first setup
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

`.env` essentials:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hyperlocal-jerry.duckdns.org
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=hyperlocal
DB_USERNAME=hyperlocal
DB_PASSWORD=YOUR_DB_PASSWORD
```

### 4. Nginx server block
`/etc/nginx/sites-available/hyperlocal` (symlink into `sites-enabled`, then `sudo nginx -t && sudo systemctl reload nginx`):
```nginx
server {
    listen 80;
    server_name hyperlocal-jerry.duckdns.org;
    root /var/www/hyperlocal/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

### 5. HTTPS (Let's Encrypt via Certbot)
```bash
sudo apt-get install -y certbot python3-certbot-nginx
sudo certbot --nginx -d hyperlocal-jerry.duckdns.org
```
Certbot rewrites the server block to listen on 443 and auto-renews the certificate.

---

## Manual deploy (fallback if Actions is down)
```bash
ssh -i ~/.ssh/hyperlocal-key.pem ubuntu@hyperlocal-jerry.duckdns.org
cd /var/www/hyperlocal && git pull origin main && \
  composer install --no-dev --optimize-autoloader && \
  php artisan migrate --force && php artisan optimize:clear && \
  php artisan config:cache && php artisan route:cache && php artisan view:cache && \
  sudo chown -R www-data:www-data storage bootstrap/cache && \
  sudo chmod -R 775 storage bootstrap/cache
```

## Quick checks
- API: `https://hyperlocal-jerry.duckdns.org/api/categories` → JSON
- Landing page: `https://hyperlocal-jerry.duckdns.org/`
- Logs: `tail -f /var/www/hyperlocal/storage/logs/laravel.log` and `sudo tail -f /var/log/nginx/error.log`
