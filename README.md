# LEMP Docker Environment for Pontix Backend

A robust LEMP (Linux, Nginx, MySQL, PHP) stack using Docker, correctly configured for Laravel development. 

## Structure

```
lemp-docker/
├── Dockerfile              # PHP-FPM container configuration
├── docker-compose.yml      # Docker services orchestration
├── nginx/
│   └── conf.d/
│       └── default.conf    # Nginx server configuration (optimized for Laravel)
└── public/
    └── crm-marina-bali/     # The Laravel project
```

## How to Run

### 1. Build and Start Containers

```bash
docker-compose up -d
```

### 2. Check Container Status

```bash
docker-compose ps
```

### 3. View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f webserver
docker-compose logs -f db
```

### 4. Application Access Points

- **Pontix BE API**: Open your browser or use Postman at `http://localhost`
- **phpMyAdmin**: Access `http://localhost:8080` to manage your database

#### phpMyAdmin Login:

- **Server**: `db` (automatically configured)
- **Username**: `crm-marina-bali_user` atau `root`
- **Password**: `crm-marina-bali_password`

### 5. Stop Containers

```bash
docker-compose down
```

## Available Services

- **PHP-FPM** (port 9000) - PHP processor
- **Nginx** (port 80) - Web server
- **MySQL** (port 3306) - Database server
- **phpMyAdmin** (port 8080) - Database management interface

## Database Environment Variables

The database credentials for the containers are now defined directly inside the `docker-compose.yml` file under the `db` environment section:

- `MYSQL_DATABASE`: `crm-marina-bali_db`
- `MYSQL_USER`: `crm-marina-bali_user`
- `MYSQL_PASSWORD`: `crm-marina-bali_password`

If you want to change these credentials, simply update the `docker-compose.yml` and restart the containers.

Also, remember to configure the `.env` file within the Laravel folder (`public/crm-marina-bali/.env`) to connect to this database:
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=crm-marina-bali_db
DB_USERNAME=crm-marina-bali_user
DB_PASSWORD=crm-marina-bali_password
```

## Included Features Out Off the Box

- Vite Dev Server: Automatically runs allowing you to use HMR by simply running `npm run dev` out of the box in combination with `docker-compose up -d`.
- 1GB Large File Uploads Settings: Nginx and PHP instances are ready to securely upload up to 1GB files without timing out. To ensure this features applies, any time you change PHP or Nginx containers, please use the rebuild command below.

## Running Artisan and other Commands

We have shell scripts available for convenience so you don't have to enter the container manually:
- `./docker-artisan.sh <command>` to run Artisan (e.g., `./docker-artisan.sh migrate`)
- `./docker-composer.sh <command>` to run Composer (e.g., `./docker-composer.sh install`)

## Troubleshooting

### If port 80 is already in use by another program

Edit `docker-compose.yml` and change the port mapping for Nginx:

```yaml
ports:
  - "8080:80" # Use port 8080 locally instead 
```

### Complete Rebuild

If you change core configurations such as Nginx or PHP, or just want a fresh empty database, you can rebuild:

```bash
docker-compose down -v  # This stops containers and removes their data volumes
docker-compose build --no-cache # This natively runs large file configurations setup in the Dockerfile
docker-compose up -d
```
