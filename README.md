
# Docker WordPress + Nginx

A lightweight Docker-based setup for running WordPress with Nginx and PHP-FPM. Ideal for local development or quick deployment scenarios.

## 🚀 Features

- WordPress served via Nginx and PHP-FPM
- MySQL database container included
- Docker Compose for easy orchestration
- Clean separation of services

## 🧰 Tech Stack

- Docker & Docker Compose
- Nginx
- PHP-FPM
- WordPress
- MariaDB (instead of MySQL)

## 📦 Folder Structure

```text
docker-wordpress-nginx/
├── .env
├── docker-compose.yml
├── nginx/
│   └── default.conf
├── php/
│   └── Dockerfile
```
- `.env`: Environment variables for WordPress and MariaDB configuration
- `docker-compose.yml`: Defines services for WordPress, MariaDB, Nginx, and PHP-FPM
- `nginx/default.conf`: Nginx configuration for serving WordPress
- `php/Dockerfile`: Custom PHP-FPM Dockerfile

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
This will build and launch the WordPress, Nginx, PHP-FPM, and MariaDB containers in detached mode.
4. Access your WordPress site
Open your browser and visit:
-   [http://localhost](https://localhost/)
-   or [http://127.0.0.1](https://127.0.0.1/)
5. Stop and remove containers
To stop the stack and remove all containers and volumes:
```text
docker compose down -v
```

## 🗄️Database Configuration
The setup includes a **MariaDB** container with default credentials (customizable via `.env`):
-   **Database Name**: `wordpress`
-   **Username**: `wordpress`
-   **Password**: `wordpress`
-   **Root Password**: `root`

## 🤝 Contributing
Pull requests are welcome! For major changes, please open an issue first to discuss what you’d like to change.

## 📄 License
This project is open source and available under the MIT License.
