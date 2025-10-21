# Laravel Backend - Quick Start Guide

## 🚀 Two Ways to Run the Backend

### Option 1: Automated Setup (Easiest)

```bash
cd /Users/ridafakherlden/www/gps-track-backend
./run-backend.sh
```

That's it! The script will:
- Install Laravel
- Start Docker services (PostgreSQL, Redis, etc.)
- Install all dependencies
- Run migrations
- Create admin user

### Option 2: Manual Setup (Step by Step)

---

## 📋 Prerequisites

- **Docker Desktop** (for Docker Compose method)
- **OR** PHP 8.2+, Composer, PostgreSQL (for manual method)

---

## 🐳 Method A: Docker Compose (Recommended)

### Step 1: Install Laravel

```bash
cd /Users/ridafakherlden/www/gps-track-backend

# Install Laravel
composer create-project laravel/laravel . --prefer-dist
```

### Step 2: Start Docker Services

```bash
# Start all services (PostgreSQL, Redis, Kafka, etc.)
docker-compose up -d

# Check services are running
docker-compose ps
```

You should see:
- ✅ gps_postgres (PostgreSQL)
- ✅ gps_redis (Redis)
- ✅ gps_kafka (Message broker)
- ✅ gps_laravel (Laravel app)

### Step 3: Install Dependencies

```bash
# Install PHP packages
docker-compose exec laravel composer install

# Install core packages
docker-compose exec laravel composer require orchid/platform
docker-compose exec laravel composer require spatie/laravel-permission
docker-compose exec laravel composer require laravel/sanctum
docker-compose exec laravel composer require mstaack/laravel-postgis
docker-compose exec laravel composer require laravel/horizon
docker-compose exec laravel composer require beyondcode/laravel-websockets
```

### Step 4: Configure Environment

```bash
# Copy environment file
docker-compose exec laravel cp .env.example .env

# Generate app key
docker-compose exec laravel php artisan key:generate

# The .env is already configured for Docker in docker-compose.yml
```

### Step 5: Run Migrations

```bash
# Run database migrations
docker-compose exec laravel php artisan migrate

# Install Orchid
docker-compose exec laravel php artisan orchid:install
```

### Step 6: Create Admin User

```bash
docker-compose exec laravel php artisan orchid:admin admin admin@example.com password
```

### Step 7: Access the Application

Open your browser:

- **API**: http://localhost:8000
- **Admin Panel**: http://localhost:8000/admin
  - Email: `admin@example.com`
  - Password: `password`

---

## 💻 Method B: Manual Installation (Without Docker)

### Step 1: Install Prerequisites

```bash
# macOS
brew install php@8.2 composer postgresql redis

# Start PostgreSQL
brew services start postgresql

# Start Redis
brew services start redis
```

### Step 2: Create Database

```bash
# Create PostgreSQL database
psql postgres
CREATE DATABASE gps_track;
CREATE USER gps_user WITH PASSWORD 'gps_password';
GRANT ALL PRIVILEGES ON DATABASE gps_track TO gps_user;
\q

# Install PostGIS
psql gps_track
CREATE EXTENSION postgis;
\q
```

### Step 3: Install Laravel

```bash
cd /Users/ridafakherlden/www/gps-track-backend

# Install Laravel
composer create-project laravel/laravel . --prefer-dist

# Install packages
composer require orchid/platform
composer require spatie/laravel-permission
composer require laravel/sanctum
composer require mstaack/laravel-postgis
composer require laravel/horizon
composer require predis/predis
composer require beyondcode/laravel-websockets
```

### Step 4: Configure Environment

```bash
cp .env.example .env
```

Edit `.env`:

```env
APP_NAME="GPS Track Backend"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=gps_track
DB_USERNAME=gps_user
DB_PASSWORD=gps_password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

BROADCAST_DRIVER=pusher
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

PUSHER_APP_ID=gps-track-id
PUSHER_APP_KEY=gps-track-key
PUSHER_APP_SECRET=gps-track-secret
```

### Step 5: Generate Key & Migrate

```bash
# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Install Orchid
php artisan orchid:install

# Create admin user
php artisan orchid:admin admin admin@example.com password
```

### Step 6: Start Services

Open **4 terminal tabs**:

**Tab 1 - Laravel Server:**
```bash
cd /Users/ridafakherlden/www/gps-track-backend
php artisan serve
# Runs on http://localhost:8000
```

**Tab 2 - Queue Worker (Horizon):**
```bash
cd /Users/ridafakherlden/www/gps-track-backend
php artisan horizon
# Or use: php artisan queue:work
```

**Tab 3 - WebSockets:**
```bash
cd /Users/ridafakherlden/www/gps-track-backend
php artisan websockets:serve
# Runs on ws://localhost:6001
```

**Tab 4 - Scheduler (Optional):**
```bash
cd /Users/ridafakherlden/www/gps-track-backend
# For development, run scheduler every minute
watch -n 60 php artisan schedule:run
```

---

## 🧪 Testing the Installation

### Test API

```bash
# Test API endpoint
curl http://localhost:8000/api/v1/devices

# Should return 401 Unauthorized (expected - means it's working)
```

### Test Admin Panel

1. Visit: http://localhost:8000/admin
2. Login with:
   - Email: `admin@example.com`
   - Password: `password`
3. You should see the Orchid dashboard

### Test Database Connection

```bash
# Using Docker
docker-compose exec laravel php artisan tinker
>>> DB::connection()->getPdo();
>>> exit

# Without Docker
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

---

## 🔧 Useful Commands

### Docker Commands

```bash
# View logs
docker-compose logs -f laravel

# Restart services
docker-compose restart

# Stop all services
docker-compose down

# Stop and remove volumes (CAUTION: deletes data)
docker-compose down -v

# Run artisan commands
docker-compose exec laravel php artisan migrate
docker-compose exec laravel php artisan tinker

# Access Laravel container shell
docker-compose exec laravel bash
```

### Artisan Commands

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# List routes
php artisan route:list

# Create model
php artisan make:model Device -m

# Create controller
php artisan make:controller Api/V1/DeviceController --api

# Run migrations
php artisan migrate
php artisan migrate:fresh  # CAUTION: drops all tables

# Seed database
php artisan db:seed
```

---

## 🌐 Access Points

| Service | URL | Notes |
|---------|-----|-------|
| **API** | http://localhost:8000 | REST API endpoints |
| **Admin Panel** | http://localhost:8000/admin | Orchid admin |
| **WebSocket** | ws://localhost:6001 | Real-time updates |
| **Horizon** | http://localhost:8000/horizon | Queue dashboard |
| **PostgreSQL** | localhost:5432 | Database |
| **Redis** | localhost:6379 | Cache/Queue |
| **Kafka** | localhost:9092 | Message broker |
| **Traccar** | http://localhost:8082 | GPS server |

---

## 🐛 Troubleshooting

### Port Already in Use

```bash
# Find and kill process on port 8000
lsof -ti:8000 | xargs kill -9

# Or use different port
php artisan serve --port=8001
```

### Permission Errors

```bash
# Fix permissions
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

### Database Connection Failed

```bash
# Check PostgreSQL is running
psql --version
pg_isready

# Check database exists
psql -U postgres -l

# Reset database
php artisan migrate:fresh
```

### Composer Issues

```bash
# Clear composer cache
composer clear-cache

# Update dependencies
composer update

# Install with no scripts
composer install --no-scripts
```

### Docker Issues

```bash
# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# View container logs
docker-compose logs -f

# Check container status
docker-compose ps
```

---

## 📱 Connect Frontend

Once the backend is running, configure your frontend:

```bash
cd /Users/ridafakherlden/www/gps-track

# Create .env.local
echo "REACT_APP_API_URL=http://localhost:8000/api/v1
REACT_APP_WS_HOST=localhost
REACT_APP_WS_PORT=6001
REACT_APP_WS_KEY=gps-track-key" > .env.local

# Start frontend
npm start
```

Visit http://localhost:3000

---

## 🎯 Next Steps

1. ✅ Backend is running
2. ⬜ Create API routes (see `routes/api.php`)
3. ⬜ Create models and migrations
4. ⬜ Implement controllers
5. ⬜ Set up WebSocket events
6. ⬜ Configure Kafka consumers
7. ⬜ Connect GPS devices

---

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Orchid Documentation](https://orchid.software/en/docs)
- [Docker Compose Reference](https://docs.docker.com/compose/)
- [PostGIS Documentation](https://postgis.net/documentation/)

---

**Need help?** Check [SETUP.md](./SETUP.md) for detailed instructions or open an issue on GitHub.

**Backend is ready!** 🚀 Now you can start building your API endpoints and connecting your frontend.

