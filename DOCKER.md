# Docker Setup Guide

This guide explains how to run the Laravel + Vue project using Docker.

## Prerequisites

- [Docker](https://www.docker.com/get-started) (version 20.10 or higher)
- [Docker Compose](https://docs.docker.com/compose/install/) (version 2.0 or higher)

## Quick Start

### Development Mode

1. **Clone the repository** (if not already done):

   ```bash
   git clone <repository-url>
   cd Project_laravel_vue
   ```

2. **Start the development environment**:

   ```bash
   docker-compose up -d
   ```

3. **Access the applications**:

   - **Vue Frontend**: http://localhost:3000
   - **Laravel API**: http://localhost:8000

4. **View logs**:

   ```bash
   # All services
   docker-compose logs -f

   # Specific service
   docker-compose logs -f laravel-api
   docker-compose logs -f vue-frontend
   ```

5. **Stop the services**:
   ```bash
   docker-compose down
   ```

### Production Mode

1. **Build and start production containers**:

   ```bash
   docker-compose -f docker-compose.prod.yml up -d --build
   ```

2. **Access the applications**:
   - **Vue Frontend**: http://localhost:3000
   - **Laravel API**: http://localhost:8000

## Project Structure

```
Project_laravel_vue/
├── laravel_api/
│   ├── Dockerfile              # Laravel API Dockerfile
│   ├── .dockerignore           # Files to exclude from Docker context
│   └── docker/
│       └── nginx/
│           └── default.conf    # Nginx config for Laravel (production)
├── vue_frontend/
│   ├── Dockerfile              # Vue frontend Dockerfile
│   ├── .dockerignore           # Files to exclude from Docker context
│   └── nginx.conf              # Nginx config for Vue SPA
├── docker-compose.yml          # Development configuration
└── docker-compose.prod.yml     # Production configuration
```

## Docker Services

### Laravel API (`laravel-api`)

- **Base Image**: PHP 8.2-FPM
- **Port**: 8000 (mapped to internal port 9000)
- **Database**: SQLite (default) or MySQL (optional)
- **Features**:
  - Hot reload in development mode
  - Optimized multi-stage build for production
  - Automatic migrations and cache optimization

### Vue Frontend (`vue-frontend`)

- **Base Image**: Node 20 (build), Nginx Alpine (production)
- **Port**: 3000 (development: 5173, production: 80)
- **Features**:
  - Hot reload in development mode
  - Optimized static file serving with Nginx
  - API proxy configuration
  - SPA routing support

### MySQL Database (`db`) - Optional

- **Base Image**: MySQL 8.0
- **Port**: 3306
- **Credentials**:
  - Database: `laravel`
  - Username: `laravel`
  - Password: `password`
  - Root Password: `root`

## Common Commands

### Docker Compose Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# Rebuild containers
docker-compose up -d --build

# View running containers
docker-compose ps

# Execute commands in containers
docker-compose exec laravel-api php artisan migrate
docker-compose exec laravel-api php artisan tinker
docker-compose exec vue-frontend npm install <package>

# Remove all containers, networks, and volumes
docker-compose down -v
```

### Laravel Artisan Commands

```bash
# Run migrations
docker-compose exec laravel-api php artisan migrate

# Seed database
docker-compose exec laravel-api php artisan db:seed

# Clear cache
docker-compose exec laravel-api php artisan cache:clear
docker-compose exec laravel-api php artisan config:clear
docker-compose exec laravel-api php artisan route:clear
docker-compose exec laravel-api php artisan view:clear

# Generate application key
docker-compose exec laravel-api php artisan key:generate

# Create storage link
docker-compose exec laravel-api php artisan storage:link
```

### Vue/NPM Commands

```bash
# Install new package
docker-compose exec vue-frontend npm install <package-name>

# Run build
docker-compose exec vue-frontend npm run build

# Run tests
docker-compose exec vue-frontend npm run test
```

## Environment Configuration

### Laravel API

1. **Copy environment file**:

   ```bash
   cp laravel_api/.env.example laravel_api/.env
   ```

2. **Update database configuration** in `laravel_api/.env`:

   **For SQLite (default)**:

   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=/var/www/html/database/database.sqlite
   ```

   **For MySQL**:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=laravel
   DB_PASSWORD=password
   ```

3. **Update CORS and Sanctum settings**:
   ```env
   APP_URL=http://localhost:8000
   FRONTEND_URL=http://localhost:3000
   SESSION_DOMAIN=localhost
   SANCTUM_STATEFUL_DOMAINS=localhost:3000
   ```

### Vue Frontend

Create `vue_frontend/.env` if needed:

```env
VITE_API_URL=http://localhost:8000
```

## Switching Between SQLite and MySQL

### To use MySQL:

1. **Uncomment MySQL service** in `docker-compose.yml`:

   ```yaml
   db:
     image: mysql:8.0
     # ... (uncomment the entire db service)
   ```

2. **Update Laravel `.env`** file as shown above

3. **Restart services**:

   ```bash
   docker-compose down
   docker-compose up -d
   ```

4. **Run migrations**:
   ```bash
   docker-compose exec laravel-api php artisan migrate
   ```

## Troubleshooting

### Port Already in Use

If ports 3000 or 8000 are already in use, modify the port mappings in `docker-compose.yml`:

```yaml
services:
  laravel-api:
    ports:
      - "8001:9000" # Change 8000 to 8001

  vue-frontend:
    ports:
      - "3001:5173" # Change 3000 to 3001
```

### Permission Issues (Linux/Mac)

If you encounter permission issues with Laravel storage:

```bash
docker-compose exec laravel-api chmod -R 777 storage bootstrap/cache
```

### Container Won't Start

1. **Check logs**:

   ```bash
   docker-compose logs laravel-api
   docker-compose logs vue-frontend
   ```

2. **Rebuild containers**:

   ```bash
   docker-compose down
   docker-compose up -d --build
   ```

3. **Remove volumes and start fresh**:
   ```bash
   docker-compose down -v
   docker-compose up -d --build
   ```

### Database Connection Issues

1. **Verify database service is running**:

   ```bash
   docker-compose ps
   ```

2. **Check Laravel can connect**:

   ```bash
   docker-compose exec laravel-api php artisan migrate:status
   ```

3. **Verify environment variables**:
   ```bash
   docker-compose exec laravel-api php artisan config:show database
   ```

### Hot Reload Not Working

1. **For Vue**: Ensure you're accessing via `http://localhost:3000` (not `127.0.0.1`)

2. **For Laravel**: Changes should reflect immediately. If not, clear cache:
   ```bash
   docker-compose exec laravel-api php artisan config:clear
   ```

## Production Deployment

### Building for Production

1. **Build production images**:

   ```bash
   docker-compose -f docker-compose.prod.yml build
   ```

2. **Start production services**:

   ```bash
   docker-compose -f docker-compose.prod.yml up -d
   ```

3. **Run optimizations**:
   ```bash
   docker-compose -f docker-compose.prod.yml exec laravel-api php artisan optimize
   ```

### Environment Variables for Production

Update `laravel_api/.env` for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Use strong random key
APP_KEY=base64:...

# Production database
DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=laravel_prod
DB_USERNAME=laravel_prod
DB_PASSWORD=<strong-password>
```

## Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/)
- [Nginx Documentation](https://nginx.org/en/docs/)

## Support

For issues or questions:

1. Check the logs: `docker-compose logs -f`
2. Review this documentation
3. Check Docker and service status: `docker-compose ps`
