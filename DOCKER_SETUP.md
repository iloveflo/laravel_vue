# Docker Setup Guide

This project uses Docker to containerize both the Laravel backend and Vue.js frontend applications.

## 🐳 Docker Images Used

### Backend (Laravel API)

- **Base Image**: `shinsenter/laravel:latest`
- **Features**:
  - Pre-configured PHP-FPM + Nginx
  - Optimized for Laravel applications
  - Multi-stage build support
  - Production-ready configuration

### Frontend (Vue.js)

- **Build Stage**: `node:20-alpine`
- **Production Stage**: `nginx:alpine`
- **Features**:
  - Optimized build process
  - Lightweight production image
  - Custom nginx configuration

## 📁 File Structure

```
Project_laravel_vue/
├── laravel_api/
│   ├── Dockerfilebe          # Backend Dockerfile
│   └── ...                   # Laravel application files
├── vue_frontend/
│   ├── Dockerfilefe          # Frontend Dockerfile
│   ├── nginx.conf            # Nginx configuration
│   └── ...                   # Vue application files
├── docker-compose.prod.yml   # Production compose file
└── docker-compose.yml        # Development compose file
```

## 🚀 Quick Start

### Production Build

```bash
# Build and start all services
docker-compose -f docker-compose.prod.yml up -d --build

# View logs
docker-compose -f docker-compose.prod.yml logs -f

# Stop services
docker-compose -f docker-compose.prod.yml down
```

### Development Build

```bash
# Build and start all services
docker-compose up -d --build

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

## 🌐 Access Points

After starting the containers:

- **Frontend (Vue.js)**: http://localhost:3000
- **Backend API (Laravel)**: http://localhost:8000
- **Health Check**: http://localhost:8000/api/health

## 🔧 Configuration Details

### Backend (Dockerfilebe)

**Production Stage:**

- Installs production dependencies only
- Runs database migrations
- Caches configuration, routes, and views
- Creates storage symlink
- Optimized autoloader

**Development Stage:**

- Installs all dependencies including dev packages
- No caching for easier development
- Hot-reload friendly

**Key Features:**

- SQLite database support (default)
- Automatic `.env` creation from `.env.example`
- Application key generation
- Proper file permissions
- Health check endpoint

### Frontend (Dockerfilefe)

**Production Stage:**

- Multi-stage build for smaller image size
- Optimized npm dependencies
- Production build with Vite
- Custom nginx configuration
- Gzip compression enabled
- API proxy to backend

**Development Stage:**

- Hot-reload with Vite dev server
- Exposed on port 5173
- All dev dependencies included

### Docker Compose (docker-compose.prod.yml)

**Services:**

1. **laravel-api**

   - Port: 8000
   - Includes nginx + PHP-FPM
   - SQLite database
   - Health checks enabled

2. **vue-frontend**
   - Port: 3000
   - Nginx serving static files
   - Proxies API requests to backend
   - Depends on laravel-api health

**Volumes:**

- `laravel-storage`: Persistent storage for uploaded files
- `laravel-db`: SQLite database persistence

**Networks:**

- `app-network`: Bridge network for inter-service communication

## 🔍 Health Checks

Both services include health checks:

**Backend:**

```bash
curl http://localhost:8000/api/health
```

**Frontend:**

```bash
curl http://localhost:3000/
```

## 🛠️ Common Commands

### View Running Containers

```bash
docker-compose -f docker-compose.prod.yml ps
```

### Execute Commands in Container

```bash
# Laravel artisan commands
docker-compose -f docker-compose.prod.yml exec laravel-api php artisan migrate

# NPM commands in frontend
docker-compose -f docker-compose.prod.yml exec vue-frontend npm run build
```

### View Container Logs

```bash
# All services
docker-compose -f docker-compose.prod.yml logs -f

# Specific service
docker-compose -f docker-compose.prod.yml logs -f laravel-api
```

### Rebuild Specific Service

```bash
docker-compose -f docker-compose.prod.yml up -d --build laravel-api
```

### Clean Up

```bash
# Stop and remove containers, networks
docker-compose -f docker-compose.prod.yml down

# Also remove volumes (WARNING: deletes data)
docker-compose -f docker-compose.prod.yml down -v

# Remove all unused Docker resources
docker system prune -a
```

## 🔐 Environment Variables

### Backend (.env)

Key variables for Docker deployment:

```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
```

### Frontend

API endpoint is configured in `nginx.conf` to proxy to `http://laravel-api:8000`

## 📝 Notes

1. **shinsenter/laravel Image**: This image combines nginx and PHP-FPM in a single container, eliminating the need for a separate nginx service for the backend.

2. **SQLite**: Default database is SQLite for simplicity. To use MySQL, uncomment the MySQL service in `docker-compose.prod.yml` and update the Laravel `.env` file.

3. **Permissions**: The Dockerfiles handle proper file permissions for Laravel storage and cache directories.

4. **Production Optimization**: The production builds include:

   - Composer autoload optimization
   - Laravel config/route/view caching
   - Optimized npm builds
   - Gzip compression

5. **Development**: For development, use `docker-compose.yml` which includes hot-reload and debugging tools.

## 🐛 Troubleshooting

### Container won't start

```bash
# Check logs
docker-compose -f docker-compose.prod.yml logs laravel-api

# Rebuild without cache
docker-compose -f docker-compose.prod.yml build --no-cache laravel-api
```

### Permission issues

```bash
# Fix Laravel permissions
docker-compose -f docker-compose.prod.yml exec laravel-api chown -R www-data:www-data /var/www/html/storage
docker-compose -f docker-compose.prod.yml exec laravel-api chmod -R 755 /var/www/html/storage
```

### Database issues

```bash
# Run migrations
docker-compose -f docker-compose.prod.yml exec laravel-api php artisan migrate:fresh --seed
```

### Frontend can't connect to API

- Check that `nginx.conf` proxy settings point to `http://laravel-api:8000`
- Verify both containers are on the same network
- Check backend health: `curl http://localhost:8000/api/health`

## 📚 Additional Resources

- [shinsenter/laravel Documentation](https://hub.docker.com/r/shinsenter/laravel)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Vue.js Production Deployment](https://vuejs.org/guide/best-practices/production-deployment.html)
