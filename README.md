![Build Status](https://github.com/micro-pi/docker-wordpress-nginx/actions/workflows/docker-ci.yml/badge.svg?branch=main)

# 🐳 Docker WordPress + Nginx

A lightweight Docker-based setup for running **WordPress** with **Nginx** and **PHP-FPM**.  
Ideal for **local development**, **testing**, or **quick deployment** scenarios.

## 🚀 Features

- WordPress served via **Nginx** and **PHP-FPM**
- WordPress core and third-party plugins/themes installed via **Composer + WPackagist**, pinned to exact versions — nothing but our own code is committed to this repo
- **MariaDB** container included (drop-in MySQL replacement)
- **Adminer** lightweight web-based database browser
- Managed through **Docker Compose** for easy orchestration
- Clean, modular structure for extending or customizing services
- **Pinned image and package versions** for reproducible, stable builds
- **Health checks** on all services for automatic failure detection
- **Auto-restart** policies (`unless-stopped`) for all containers
- **Gzip compression** and **security headers** via Nginx
- **OPcache** fully configured for PHP performance
- **Hardened security**: database port bound to localhost only, `wp-config.php` kept outside the web-servable docroot, all secrets read from environment variables (nothing hardcoded)

## 🧰 Tech Stack

- Docker & Docker Compose
- Nginx
- PHP-FPM
- WordPress
- MariaDB
- Adminer (for DB management)

## 📦 Folder Structure

```text
docker-wordpress-nginx/
├── .env                    # Environment variables (copy from .env.example)
├── .env.example            # Template with documented environment variables
├── .gitmodules             # Our own plugins, tracked as git submodules
├── docker-compose.yml      # Base services: WordPress, MariaDB, Nginx, PHP-FPM, Adminer
├── docker-compose.dev.yml  # Dev overlay: live-editable Mpi* plugin mounts
├── docker-compose.prod.yml # Prod overlay
├── .github/                # GitHub Actions CI/CD configuration
│    └── workflows/
│        └── docker-ci.yml  # GitHub workflow for building/testing Docker images
├── nginx/                  # Nginx service
│   ├── .dockerignore       # Files excluded from the Nginx Docker build context
│   ├── default.conf        # Nginx configuration for serving WordPress
│   └── Dockerfile          # Custom Nginx image
└── php/                    # PHP-FPM service
    ├── Dockerfile          # Multi-stage: composer install, then the runtime image
    ├── composer.json       # WordPress core + third-party plugin/theme versions
    ├── composer.lock       # Locked, exact resolved versions (commit this)
    ├── wp-config.php       # Env-var-driven config; not tracked inside wordpress/
    └── wordpress/          # Composer-managed; only wp-content/plugins/Mpi* is tracked
        └── wp-content/plugins/
            ├── MpiAbstractClass/               # git submodule
            ├── MpiBreadcrumb/                  # git submodule
            ├── MpiCommentImages/                # git submodule
            ├── MpiCommentReplyEmailNotification/ # git submodule
            └── MpiDomain301Redirects/          # git submodule
```

WordPress core, third-party plugins, and the theme are **not** committed —
they're resolved by Composer from `php/composer.json`/`composer.lock` at
build time (see [Managing WordPress Core & Plugin Versions](#-managing-wordpress-core--plugin-versions)
below). Only our own `Mpi*` plugins (submodules) and `wp-config.php` live in
this repo.

## 🛠️ Getting Started
 1. Clone the repository (with submodules — our own plugins are tracked that way)
```text
git clone --recurse-submodules https://github.com/micro-pi/docker-wordpress-nginx.git
cd docker-wordpress-nginx
```
Already cloned without `--recurse-submodules`? Run `git submodule update --init --recursive`.

2. Configure your environment
Copy `.env.example` to `.env` and set your credentials:
```text
cp .env.example .env
```
Then edit `.env` with your values:
```text
# NGINX Configuration
NGINX_PORT=8081

# MariaDB settings
MYSQL_ROOT_PASSWORD=your_root_password
MYSQL_DATABASE=wordpress_db
MYSQL_USER=wordpress_user
MYSQL_PASSWORD=your_db_password

# Database port (bound to localhost only)
DB_PORT=3307

# Adminer port
ADMINER_PORT=8181

# WordPress table prefix, debug flag, and auth keys/salts — wp-config.php
# reads all of these from the environment, nothing is hardcoded. Generate
# real values with `openssl rand -hex 32` (one per key/salt) — see
# .env.example for the full list of WORDPRESS_* variables required.
```
> ⚠️ **Security:** Never commit your `.env` file. Use strong, unique passwords and secret keys outside local dev — see `.env.example` for the complete set of required variables.

3. Build and Start the Docker containers
```text
docker compose up -d --build
```
This builds and launches **WordPress**, **Nginx**, **PHP-FPM**, **MariaDB**, and **Adminer** containers in detached mode. The `php` build resolves WordPress core and third-party plugins/themes via Composer — see [Managing WordPress Core & Plugin Versions](#-managing-wordpress-core--plugin-versions).

To check running containers:
```text
docker compose ps
```
**🚀 Running Containers**
```text
NAME              IMAGE              COMMAND                  SERVICE   CREATED          STATUS                    PORTS
wp_adminer_prod   adminer:4.8.1      "entrypoint.sh docke…"   adminer   10 minutes ago   Up 9 minutes (healthy)    0.0.0.0:8181->8080/tcp
wp_db_prod        mariadb:11.0       "docker-entrypoint.s…"   db        10 minutes ago   Up 9 minutes (healthy)    127.0.0.1:3307->3306/tcp
wp_nginx_prod     wp_nginx:latest    "/docker-entrypoint.…"   nginx     9 minutes ago    Up 9 minutes (healthy)    0.0.0.0:8081->80/tcp
wp_php_prod       wp_php:latest      "docker-php-entrypoi…"   php       10 minutes ago   Up 9 minutes (healthy)    9000/tcp
```

4. Access your WordPress site
Open your browser and visit `http://localhost:${NGINX_PORT}` (default: [http://localhost:8081](http://localhost:8081/)). A fresh database redirects straight to the WordPress installer.

5. Stop and clean up
To stop the stack and remove all containers and volumes:
```text
docker compose down -v
```

## 🧪 DEV Mode (Live Local Editing)

The setup now uses one shared base file (`docker-compose.yml`) and small suffix overlays:
- `docker-compose.prod.yml` -> suffix `prod`
- `docker-compose.dev.yml` -> suffix `dev`

This keeps one source of truth for services while generating unique project and container names per environment.

> **What dev mode bind-mounts, and what it doesn't:** only the `Mpi*` plugin
> directories are bind-mounted for live editing. WordPress core, third-party
> plugins, the theme, and `wp-config.php` always come from the built image.
> This is intentional — `composer install` treats its install directory as
> exclusively its own and deletes anything else living there, so the whole
> `php/wordpress` tree can never be bind-mounted as a unit without composer
> wiping the `Mpi*` submodules on the next install. After changing a version
> in `composer.json` or editing `wp-config.php`, rebuild the image (step 2
> below) — and if a stack is already running, recreate its volumes too
> (`docker compose -f docker-compose.yml -f docker-compose.dev.yml down -v`
> then `up -d`), since a named volume only seeds from the image once.

1. Start in dev mode:
```text
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d
```

2. Rebuild only when Dockerfiles or image dependencies change:
```text
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
```

3. Stop dev mode:
```text
docker compose -f docker-compose.yml -f docker-compose.dev.yml down
```

4. Start prod mode:
```text
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

5. Confirm resolved names/config:
```text
docker compose -f docker-compose.yml -f docker-compose.dev.yml config
docker compose -f docker-compose.yml -f docker-compose.prod.yml config
```

Example generated container names:
- DEV: `wp_nginx_dev`, `wp_php_dev`, `wp_db_dev`, `wp_adminer_dev`
- PROD: `wp_nginx_prod`, `wp_php_prod`, `wp_db_prod`, `wp_adminer_prod`

> Note: container-name conflicts are solved by suffixes. If you run DEV and PROD at the same time, ensure host ports are different between the two runs.

## 📦 Managing WordPress Core & Plugin Versions

WordPress core and every third-party plugin/theme are resolved by
[Composer](https://getcomposer.org/) from [WPackagist](https://wpackagist.org/)
— none of that source is committed to this repo. `php/composer.json` pins
each one to an exact version; `php/composer.lock` records what actually got
resolved and is committed so builds are reproducible.

**To bump a version:**
1. Edit the version string in `php/composer.json` (e.g. `"wpackagist-plugin/akismet": "5.8"`).
2. Regenerate the lock file:
   ```text
   docker run --rm -v "${PWD}/php:/app" -w /app composer:2 composer update <package/name> --no-scripts
   ```
   (omit `<package/name>` to update everything to what `composer.json` allows)
3. Commit both `composer.json` and the updated `composer.lock`.
4. Rebuild: `docker compose build php`, then recreate the running stack's volumes so it actually picks up the change (see the dev-mode note above).

**To add a new third-party plugin/theme:** find its slug on
[wpackagist.org](https://wpackagist.org/), add
`"wpackagist-plugin/<slug>": "<version>"` (or `wpackagist-theme/<slug>`) to
`composer.json`, then regenerate the lock file and rebuild as above.

Our own plugins (`Mpi*`) are **not** managed by Composer — they're git
submodules pointing at their own repos, edited and versioned independently.

### 🔹 WordPress

**Setup Configuration**
![WordPress Setup Configuration File](screenshots/wordpress-setup-configuration-file.png)

**Dashboard**
![WordPress Dashboard](screenshots/wordpress-dashboard.png)

**Home Page**
![WordPress Home Page](screenshots/wordpress-home-page.png)

## 🐳 Docker Status

Below you can see the local Docker environment after building the stack.

### 🧱 Images
```text
PS D:\workspaces\docker-workspace\docker-wordpress-nginx> docker images
REPOSITORY   TAG       IMAGE ID       CREATED         SIZE
wp_php       latest    c48d427debfc   7 minutes ago   822MB
wp_nginx     latest    dc0cdfddea42   30 hours ago    225MB
adminer      4.8.1     b1d44e230bed   11 days ago     168MB
mariadb      11.0      5b6a1eac15b8   2 months ago    456MB
```

#### 📸 Docker Desktop – Images View
![Docker Desktop – Images View](screenshots/docker-desktop-images.png)

### 🚀 Running Containers
```text
PS D:\workspaces\docker-workspace\docker-wordpress-nginx> docker ps
CONTAINER ID   IMAGE              COMMAND                  CREATED         STATUS                   PORTS                                         NAMES
9a5ef764da35   wp_nginx:latest    "/docker-entrypoint.…"   7 minutes ago   Up 7 minutes (healthy)   0.0.0.0:8081->80/tcp                          wp_nginx_prod
a0dbf4566285   adminer:4.8.1      "entrypoint.sh docke…"   7 minutes ago   Up 7 minutes             0.0.0.0:8181->8080/tcp, [::]:8181->8080/tcp   wp_adminer_prod
8a65218f5b09   wp_php:latest      "docker-php-entrypoi…"   7 minutes ago   Up 7 minutes (healthy)   9000/tcp                                      wp_php_prod
3163cebdf6f6   mariadb:11.0       "docker-entrypoint.s…"   7 minutes ago   Up 7 minutes (healthy)   127.0.0.1:3307->3306/tcp                      wp_db_prod
```

#### 📸 Docker Desktop – Running Containers
![Docker Desktop – Running Containers](screenshots/docker-desktop-running-containers.png)

## 🗄️Database Configuration
The setup includes a **MariaDB 11.0** container with default credentials (customizable via `.env`):
**Default values (for local use):**
| Variable               | Default                    |
| ---------------------- | -------------------------- |
| `MYSQL_DATABASE`       | wordpress_db               |
| `MYSQL_USER`           | wordpress_user             |
| `MYSQL_PASSWORD`       | wordpress_password_change_me |
| `MYSQL_ROOT_PASSWORD`  | root_password_change_me    |

> 🔒 **Security:** The database port is bound to `127.0.0.1` only, preventing external network access. It is accessible on the host for tools like Adminer or DB clients, but not exposed publicly.

> ⚠️ **MariaDB only applies `MYSQL_*` credentials the first time it initializes an empty data volume.** If you change `MYSQL_USER`/`MYSQL_PASSWORD`/`MYSQL_ROOT_PASSWORD` in `.env` on a stack that's already run before, the running database keeps its old credentials until you recreate the volume: `docker compose down -v` for that environment, then `up -d` again.

`wp-config.php` no longer hardcodes any credentials or secret keys — it reads
everything from environment variables, wired through `docker-compose.yml`'s
`php` service from `.env`:

| Variable                                                                                | Purpose                                    |
| ---------------------------------------------------------------------------------------- | ------------------------------------------- |
| `WORDPRESS_DB_NAME`, `WORDPRESS_DB_USER`, `WORDPRESS_DB_PASSWORD`, `WORDPRESS_DB_HOST`, `WORDPRESS_DB_CHARSET`, `WORDPRESS_DB_COLLATE` | Database connection (mirror the `MYSQL_*` values) |
| `WORDPRESS_TABLE_PREFIX`                                                                  | WordPress table prefix (default `wp_`)      |
| `WORDPRESS_DEBUG`                                                                         | `WP_DEBUG` toggle (`true`/`false`)          |
| `WORDPRESS_AUTH_KEY`, `WORDPRESS_SECURE_AUTH_KEY`, `WORDPRESS_LOGGED_IN_KEY`, `WORDPRESS_NONCE_KEY`, `WORDPRESS_AUTH_SALT`, `WORDPRESS_SECURE_AUTH_SALT`, `WORDPRESS_LOGGED_IN_SALT`, `WORDPRESS_NONCE_SALT` | WordPress auth keys/salts — generate your own with `openssl rand -hex 32` (one per line) or the [official secret-key API](https://api.wordpress.org/secret-key/1.1/salt/); never reuse the placeholders in `.env.example` |

## ⚙️ Customization
You can tweak the following `.env` variables to fit your environment:
| Variable          | Description                             | Default     |
| ----------------- | --------------------------------------- | ----------- |
| `NGINX_PORT`      | Public port for WordPress               | `8081`      |
| `ADMINER_PORT`    | Exposed Adminer port                    | `8181`      |
| `DB_PORT`         | MariaDB port (localhost only)           | `3307`      |
| `MYSQL_DATABASE`  | WordPress database name                 | `wordpress_db` |
| `MYSQL_USER`      | WordPress database user                 | `wordpress_user` |
| `MYSQL_PASSWORD`  | WordPress database password             | *(set in .env)* |
| `MYSQL_ROOT_PASSWORD` | MariaDB root password               | *(set in .env)* |

To apply changes after editing `.env`:
```text
docker compose build
docker compose up -d
```

## 🧩 Database Browser (Adminer)
This setup includes Adminer — a lightweight, single-file database management tool for **MariaDB/MySQL**.
It provides a simple web interface to explore, query, and manage your WordPress database.

**🔹 Configuration**
Adminer is defined as a separate service in `docker-compose.yml`:
```text
adminer:
  image: adminer:4.8.1
  container_name: wp_adminer_${APP_SUFFIX:-prod}
  depends_on:
    - db
  ports:
    - ${ADMINER_PORT}:8080
  environment:
    ADMINER_DEFAULT_SERVER: db
  restart: unless-stopped
```

**🔹 Environment Variable**
Add to your `.env` file (if not already present):
```text
ADMINER_PORT=8181
```

**🔹 Usage**
1. Start the stack:
```text
docker compose up -d
```

2. Open Adminer:
- [http://localhost:8181](http://localhost:8181/)
- or [http://127.0.0.1:8181](http://127.0.0.1:8181/)

(or `http://localhost:${ADMINER_PORT}` if changed)

3. Log in using your database credentials:

| Field        | Value                                           |
| ------------ | ----------------------------------------------- |
| **System**   | MySQL                                           |
| **Server**   | db                                              |
| **Username** | `${MYSQL_USER}` or `root`                       |
| **Password** | `${MYSQL_PASSWORD}` or `${MYSQL_ROOT_PASSWORD}` |
| **Database** | `${MYSQL_DATABASE}`                             |

### 🖼️ Adminer Screenshots

**Login Page**
![Adminer Login](screenshots/adminer-login.png)

**Database Selection**
![Adminer Select Database](screenshots/adminer-select-db.png)

## 🧩 Troubleshooting
🛑 Port already in use
```text
# Change port in .env (e.g. NGINX_PORT=8082) and restart
docker compose down
docker compose up -d
```

🔑 Permission denied for WordPress files
```text
docker compose exec php chown -R www-data:www-data /var/www/html
```

⚙️ Database connection errors / `Access denied for user ...` in the `db` logs
- Ensure the db container is running: `docker compose ps`
- Check your `.env` values (`WORDPRESS_DB_HOST` should be `db`, and `WORDPRESS_DB_USER`/`WORDPRESS_DB_PASSWORD` should mirror `MYSQL_USER`/`MYSQL_PASSWORD`).
- **Most common cause:** the `db_data` volume already existed with *different* credentials baked in from an earlier run — MariaDB only applies `.env` credentials the first time it initializes an empty volume. Fix by recreating it (this deletes that environment's database, so only do this if there's nothing worth keeping in it):
  ```text
  docker compose down -v
  docker compose up -d
  ```

🧱 `docker compose build` fails on `COPY wp-config.php` / `COPY composer.lock`
- These are tracked files at `php/wp-config.php` and `php/composer.lock` — make sure they exist (see [Managing WordPress Core & Plugin Versions](#-managing-wordpress-core--plugin-versions) if `composer.lock` is missing or out of date).

🧩 A `Mpi*` plugin directory is empty after cloning
- Submodules weren't initialized: `git submodule update --init --recursive`.

🔍 View logs
```text
docker compose logs -f
```

## 🤖 Continuous Integration
This project includes a **GitHub Actions** workflow:
```text
.github/workflows/docker-ci.yml
```
It automatically:
- Builds all Docker images
- Verifies syntax and configuration
- Prepares images for deployment/testing

## 🤝 Contributing
Pull requests are welcome!
For major changes, please open an issue first to discuss what you’d like to improve.

## 📄 License
This project is open source and available under the **MIT License**.