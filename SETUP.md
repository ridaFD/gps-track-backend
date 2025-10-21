# Backend Setup Guide

Complete setup instructions for the GPS Track Backend.

## 🚀 Quick Setup (Docker Compose)

### 1. Clone & Configure

```bash
git clone https://github.com/ridaFD/gps-track-backend.git
cd gps-track-backend

# Copy environment file
cp .env.example .env

# Update .env with your settings
nano .env
```

### 2. Start Services

```bash
# Start all Docker services
docker-compose up -d

# Wait for services to be healthy
docker-compose ps
```

### 3. Install Laravel

```bash
# Install Composer dependencies
docker-compose exec laravel composer install

# Generate app key
docker-compose exec laravel php artisan key:generate

# Run migrations
docker-compose exec laravel php artisan migrate

# Install Orchid
docker-compose exec laravel php artisan orchid:install

# Create admin user
docker-compose exec laravel php artisan orchid:admin admin admin@example.com password
```

### 4. Access Services

- **Laravel API**: http://localhost:8000
- **Orchid Admin**: http://localhost:8000/admin
- **Traccar**: http://localhost:8082
- **Meilisearch**: http://localhost:7700
- **WebSockets**: ws://localhost:6001

---

## 📦 Manual Installation

### Prerequisites

- PHP >= 8.2
- Composer
- PostgreSQL 15 with PostGIS
- Redis
- Node.js & NPM

### 1. Install Laravel

```bash
composer create-project laravel/laravel gps-track-backend
cd gps-track-backend
```

### 2. Install Packages

```bash
# Core packages
composer require orchid/platform
composer require spatie/laravel-permission
composer require laravel/sanctum
composer require mstaack/laravel-postgis

# Queues & WebSockets
composer require laravel/horizon
composer require predis/predis
composer require beyondcode/laravel-websockets

# Utilities
composer require maatwebsite/excel
composer require spatie/laravel-activitylog
composer require guzzlehttp/guzzle

# Optional
composer require laravel/scout meilisearch/meilisearch-laravel
composer require laravel/cashier
composer require spatie/laravel-multitenancy
```

### 3. Publish Configurations

```bash
php artisan vendor:publish --provider="Orchid\Platform\Providers\FoundationServiceProvider"
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider"
php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider"
```

### 4. Configure Database

Edit `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=gps_track
DB_USERNAME=gps_user
DB_PASSWORD=your_password

# TimescaleDB
TIMESCALE_HOST=localhost
TIMESCALE_PORT=5433
TIMESCALE_DATABASE=gps_telemetry
```

### 5. Run Migrations

```bash
php artisan migrate
php artisan db:seed
```

### 6. Start Services

```bash
# Terminal 1 - Laravel
php artisan serve

# Terminal 2 - Horizon
php artisan horizon

# Terminal 3 - WebSockets
php artisan websockets:serve

# Terminal 4 - Queue Worker (alternative to Horizon)
php artisan queue:work
```

---

## 🗄️ Database Setup

### PostgreSQL + PostGIS

```bash
# Install PostGIS
sudo apt-get install postgresql-15-postgis-3

# Create database
createdb -U postgres gps_track

# Enable PostGIS
psql -U postgres gps_track
CREATE EXTENSION postgis;
CREATE EXTENSION postgis_topology;
\q
```

### TimescaleDB

```bash
# Install TimescaleDB
sudo add-apt-repository ppa:timescale/timescaledb-ppa
sudo apt update
sudo apt install timescaledb-postgresql-15

# Create database
createdb -U postgres gps_telemetry

# Enable TimescaleDB
psql -U postgres gps_telemetry
CREATE EXTENSION timescaledb;
\q
```

---

## 🔧 Configuration Files

### 1. Create Models

**app/Models/Device.php**
```bash
php artisan make:model Device -m
```

**app/Models/Asset.php**
```bash
php artisan make:model Asset -m
```

**app/Models/Geofence.php**
```bash
php artisan make:model Geofence -m
```

### 2. Create Controllers

```bash
php artisan make:controller Api/V1/DeviceController --api --model=Device
php artisan make:controller Api/V1/GeofenceController --api --model=Geofence
php artisan make:controller Api/V1/AlertController --api --model=Alert
```

### 3. Create Jobs

```bash
php artisan make:job ProcessPositionJob
php artisan make:job EvaluateAlertRulesJob
php artisan make:job GenerateReportJob
```

### 4. Create Events

```bash
php artisan make:event DevicePositionUpdated
php artisan make:event AlertCreated
php artisan make:event TelemetryUpdated
```

---

## 🎨 Orchid Setup

### 1. Install Orchid

```bash
php artisan orchid:install
```

### 2. Create Admin User

```bash
php artisan orchid:admin admin admin@example.com password
```

### 3. Create Screens

```bash
php artisan orchid:screen DeviceListScreen
php artisan orchid:screen DeviceEditScreen
php artisan orchid:screen GeofenceListScreen
php artisan orchid:screen AlertListScreen
```

### 4. Access Admin Panel

Visit: http://localhost:8000/admin
Login with: admin@example.com / password

---

## 🔐 API Authentication

### 1. Configure Sanctum

**config/sanctum.php**
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,localhost:3000')),
```

### 2. Add Middleware

**app/Http/Kernel.php**
```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### 3. Test API

```bash
# Login
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Get devices (with token)
curl http://localhost:8000/api/v1/devices \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 🔄 WebSocket Setup

### 1. Configure Broadcasting

**config/broadcasting.php** - Set default to `pusher`

**.env**
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

### 2. Start WebSocket Server

```bash
php artisan websockets:serve
```

### 3. Test WebSocket

```javascript
// Frontend
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'pusher',
    key: 'your-app-key',
    wsHost: 'localhost',
    wsPort: 6001,
    forceTLS: false,
});

echo.channel('device.1')
    .listen('DevicePositionUpdated', (e) => {
        console.log(e);
    });
```

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter DeviceTest

# Generate coverage
php artisan test --coverage
```

---

## 🚢 Production Deployment

### 1. Optimize Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

### 2. Set Environment

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com
```

### 3. Configure Supervisor (Queue Workers)

```bash
sudo nano /etc/supervisor/conf.d/gps-track.conf
```

```ini
[program:gps-track-horizon]
process_name=%(program_name)s
command=php /path/to/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/horizon.log
stopwaitsecs=3600
```

### 4. Configure Nginx

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    root /var/www/gps-track-backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 5. SSL with Let's Encrypt

```bash
sudo certbot --nginx -d api.yourdomain.com
```

---

## 📚 Next Steps

1. ✅ Complete this setup
2. ⬜ Connect React frontend
3. ⬜ Set up GPS device forwarding (Traccar or custom)
4. ⬜ Configure Kafka topics
5. ⬜ Implement alert rules
6. ⬜ Set up reporting
7. ⬜ Configure notifications (email, SMS, push)
8. ⬜ Deploy to production

---

## 🆘 Troubleshooting

### Common Issues

**Port Already in Use**
```bash
lsof -ti:8000 | xargs kill -9
```

**Permission Issues**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Database Connection Failed**
- Check PostgreSQL is running
- Verify credentials in `.env`
- Check firewall rules

**WebSocket Not Connecting**
- Ensure port 6001 is open
- Check `PUSHER_APP_KEY` matches in frontend
- Verify WebSocket server is running

---

For more help, see the [FAQ](./FAQ.md) or open an issue on GitHub.

