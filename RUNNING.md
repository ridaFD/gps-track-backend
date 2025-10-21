# 🚀 Laravel Backend - Now Running!

## ✅ Status: RUNNING

Your Laravel backend is currently running on:
- **URL**: http://localhost:8000
- **Status**: ✅ Active

---

## 📊 Current Setup

### What's Running
```
✅ Laravel Development Server (port 8000)
⏸️  PostgreSQL (not started yet)
⏸️  Redis (not started yet)
```

### What You Have
- ✅ Laravel 10 installed
- ✅ All dependencies installed
- ✅ Application key generated
- ✅ Environment file (`.env`) configured

---

## 🎯 Quick Actions

### Check if it's working
```bash
curl http://localhost:8000
# You should see the Laravel welcome page HTML
```

### Stop the server
```bash
pkill -f "php artisan serve"
```

### Start the server again
```bash
cd /Users/ridafakherlden/www/gps-track-backend
php artisan serve
```

### View logs (if needed)
```bash
tail -f storage/logs/laravel.log
```

---

## 🔧 Next Steps

### 1. Test the API
Open your browser and visit:
```
http://localhost:8000
```

You should see the Laravel welcome page!

### 2. Create Your First API Route
Edit `routes/api.php`:

```php
Route::get('/test', function () {
    return response()->json([
        'message' => 'GPS Track API is working!',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});
```

Then test it:
```bash
curl http://localhost:8000/api/test
```

### 3. Set Up Database (Optional)

**For quick testing with SQLite:**
```bash
touch database/database.sqlite
```

Update `.env`:
```env
DB_CONNECTION=sqlite
# Remove or comment out:
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=gps_track
# DB_USERNAME=gps_user
# DB_PASSWORD=gps_password
```

Run migrations:
```bash
php artisan migrate
```

**For PostgreSQL (Production-ready):**
```bash
# Install PostgreSQL if you haven't
brew install postgresql@15
brew services start postgresql@15

# Create database
psql postgres
CREATE DATABASE gps_track;
CREATE USER gps_user WITH PASSWORD 'gps_password';
GRANT ALL PRIVILEGES ON DATABASE gps_track TO gps_user;
\q

# Run migrations
php artisan migrate
```

### 4. Install GPS Tracking Packages

```bash
# PostGIS support for geospatial data
composer require mstaack/laravel-postgis

# WebSocket support
composer require beyondcode/laravel-websockets
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"

# Admin panel (Orchid)
composer require orchid/platform
php artisan orchid:install

# Permissions
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# API Authentication
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Queue & Jobs (Horizon)
composer require laravel/horizon
php artisan horizon:install

# Run all migrations
php artisan migrate
```

### 5. Create Admin User (Orchid)
```bash
php artisan orchid:admin admin admin@example.com password
```

Then visit: http://localhost:8000/admin

---

## 🔗 Connect Frontend

### 1. Configure Frontend Environment

In your **frontend** project (`/Users/ridafakherlden/www/gps-track`):

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

### 2. Enable CORS in Backend

Install CORS package (if not already):
```bash
composer require fruitcake/laravel-cors
```

Update `config/cors.php`:
```php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
```

### 3. Create API Routes

Edit `routes/api.php`:
```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        
        // Devices/Assets
        Route::apiResource('devices', DeviceController::class);
        
        // Geofences
        Route::apiResource('geofences', GeofenceController::class);
        
        // Positions (Telemetry)
        Route::get('/positions/last', [PositionController::class, 'last']);
        Route::get('/positions/history/{deviceId}', [PositionController::class, 'history']);
        
        // Alerts
        Route::apiResource('alerts', AlertController::class);
        
        // Reports
        Route::post('/reports/generate', [ReportController::class, 'generate']);
        Route::get('/reports/{id}', [ReportController::class, 'show']);
        
        // Statistics
        Route::get('/statistics/dashboard', [StatisticsController::class, 'dashboard']);
    });
});
```

---

## 🐛 Troubleshooting

### Permission Issues
If you see permission errors:
```bash
chmod -R 775 storage bootstrap/cache
```

### Port Already in Use
If port 8000 is busy:
```bash
# Check what's using the port
lsof -ti:8000

# Kill it
lsof -ti:8000 | xargs kill -9

# Or use a different port
php artisan serve --port=8001
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

---

## 📚 Useful Commands

### Artisan Commands
```bash
# List all routes
php artisan route:list

# Create a model with migration
php artisan make:model Device -m

# Create a controller
php artisan make:controller Api/V1/DeviceController --api

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Open Tinker (Laravel REPL)
php artisan tinker
```

### Testing
```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter=DeviceTest
```

---

## 🌐 Access Points

| Service | URL | Status |
|---------|-----|--------|
| **API** | http://localhost:8000 | ✅ Running |
| **Admin Panel** | http://localhost:8000/admin | ⏸️ Install Orchid first |
| **Horizon** | http://localhost:8000/horizon | ⏸️ Install Horizon first |
| **WebSockets** | ws://localhost:6001 | ⏸️ Start WebSocket server |

---

## 💡 Pro Tips

1. **Use Laravel Sail for Docker**: If you want a complete Docker setup, run `php artisan sail:install`
2. **Enable Query Logging**: Helpful for debugging database queries
3. **Use Laravel Debugbar**: `composer require barryvdh/laravel-debugbar --dev`
4. **API Documentation**: Consider using `composer require darkaonline/l5-swagger` for auto-generated API docs

---

## 🎉 You're All Set!

Your Laravel backend is running and ready for development. Start building your GPS tracking API!

**Need help?** Check the documentation:
- [Laravel Docs](https://laravel.com/docs)
- [Orchid Docs](https://orchid.software/en/docs)
- [Backend SETUP.md](./SETUP.md)
- [Frontend Integration Guide](../gps-track/INTEGRATION.md)

---

**Happy Coding!** 🚀

