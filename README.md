![Build Status](https://github.com/micro-pi/docker-wordpress-nginx/actions/workflows/docker-ci.yml/badge.svg?branch=main)

# 🐳 Docker WordPress + Nginx

A lightweight Docker-based setup for running **WordPress** with **Nginx** and **PHP-FPM**.  
Ideal for **local development**, **testing**, or **quick deployment** scenarios.

## 🚀 Features

- WordPress served via **Nginx** and **PHP-FPM**
- **MariaDB** container included (drop-in MySQL replacement)
- Managed through **Docker Compose** for easy orchestration
- Clean, modular structure for extending or customizing services

## 🧰 Tech Stack

- Docker & Docker Compose
- Nginx
- PHP-FPM
- WordPress
- MariaDB

## 📦 Folder Structure

```text
docker-wordpress-nginx/
├── .env                   # Environment variables for WordPress and MariaDB configuration
├── docker-compose.yml     # Defines services for WordPress, MariaDB, Nginx, and PHP-FPM
├── .github/               # GitHub Actions CI/CD configuration
│    └── workflows/
│        └──docker-ci.yml  # GitHub workflow for building/testing Docker images
├── nginx/                 # Nginx service
│   ├── default.conf       # Nginx configuration for serving WordPress
│   └── Dockerfile         # Custom Nginx image
├── php/                   # PHP-FPM service
│   ├── Dockerfile         # Custom PHP image
│   └── wordpress/         # WordPress source files
│       └── readme.txt
└── mariadb/               # (optional) MariaDB initialization scripts
    └── init.sql
```

## 🛠️ Getting Started
 1. Clone the repository
```text
git clone https://github.com/micro-pi/docker-wordpress-nginx.git
cd docker-wordpress-nginx
```

2. Configure your environment
Edit the `.env` file to set your personal database credentials:
```text
# WordPress settings
WORDPRESS_DB_NAME=your_db_name
WORDPRESS_DB_USER=your_db_user
WORDPRESS_DB_PASSWORD=your_db_password

# MariaDB settings
MYSQL_ROOT_PASSWORD=your_root_password
MYSQL_DATABASE=your_db_name
MYSQL_USER=your_db_user
MYSQL_PASSWORD=your_db_password
```

3. Build and Start the Docker containers
```text
docker compose up -d
```
This builds and launches **WordPress**, **Nginx**, **PHP-FPM**, and **MariaDB** containers in detached mode.

To check running containers:
```text
docker compose ps
```

4. Access your WordPress site
Open your browser and visit:
- [http://localhost](https://localhost/)
- or [http://127.0.0.1](https://127.0.0.1/)

5. Stop and clean up
To stop the stack and remove all containers and volumes:
```text
docker compose down -v
```

## 🗄️Database Configuration
The setup includes a **MariaDB** container with default credentials (customizable via `.env`):
**Default values (for local use):**
| Variable             | Default   |
| -------------------- | --------- |
| `MYSQL_DATABASE`     | wordpress |
| `MYSQL_USER`         | wordpress |
| `MYSQL_PASSWORD`     | wordpress |
| `MYSQL_ROOT_PASSWORD`| root      |

You can also make the database accessible externally using:
```text
DB_BIND_ADDRESS=0.0.0.0
```
## ⚙️ Customization
You can tweak the following `.env` variables to fit your environment:
| Variable          | Description               | Default     |
| ----------------- | ------------------------- | ----------- |
| `NGINX_PORT`      | Public port for WordPress | `8081`      |
| `DB_PORT`         | Exposed MariaDB port      | `3306`      |
| `DB_BIND_ADDRESS` | Database bind address     | `127.0.0.1` |

To apply changes after editing `.env`:
```text
docker compose build
docker compose up -d
```

## 🧩 Troubleshooting
🛑 Port already in use
```text
# Change port in .env (e.g. NGINX_PORT=8082) and restart
docker compose down
docker compose up -d
```

🔑 Permission denied for WordPress files
```text
sudo chown -R www-data:www-data wordpress
```

⚙️ Database connection errors
- Ensure the db container is running:
```text
docker compose ps
```
- Check your .env values (especially WORDPRESS_DB_HOST).

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