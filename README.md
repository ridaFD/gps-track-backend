# GPS Track Backend - Laravel API

Backend API and admin panel for GPS tracking system with real-time telemetry processing.

![Laravel](https://img.shields.io/badge/Laravel-11-red)
![PHP](https://img.shields.io/badge/PHP-8.2-blue)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-blue)
![License](https://img.shields.io/badge/License-MIT-green)

## 🏗️ Architecture

This backend implements a **hybrid architecture** optimized for GPS tracking:

- **Laravel** - Admin panel (Orchid), REST APIs, RBAC, reports, notifications
- **PostgreSQL + PostGIS** - Entities, geofences, routes (spatial data)
- **TimescaleDB** - High-volume telemetry (positions, sensor readings)
- **Redis** - Queues, cache, sessions, real-time state
- **Kafka/NATS** - Message bus for GPS data ingestion
- **Traccar** (Optional) - GPS device protocol handling

## 📋 Features

### Core Features
- ✅ Multi-tenant organizations
- ✅ Device/Asset management
- ✅ Real-time position tracking
- ✅ Geofencing with PostGIS
- ✅ Alert rules engine
- ✅ Trip detection & playback
- ✅ Reports & analytics
- ✅ Webhooks
- ✅ Role-based access control

### Admin Panel (Orchid Platform)
- Dashboard with fleet overview
- Device & asset CRUD
- Geofence drawing & management
- Alert rules configuration
- User & permission management
- Report generation
- Activity logs

### APIs
- RESTful API (JSON:API compliant)
- Sanctum authentication
- WebSocket support (Laravel WebSockets)
- Real-time position updates
- Pagination & filtering

## 🚀 Quick Start

### Prerequisites

- PHP >= 8.2
- Composer
- Docker & Docker Compose (recommended)
- Node.js & NPM (for asset compilation)

### Option 1: Docker Compose (Recommended)

```bash
# Clone the repository
git clone https://github.com/ridaFD/gps-track-backend.git
cd gps-track-backend

# Copy environment file
cp .env.example .env

# Start all services
docker-compose up -d

# Install dependencies
docker-compose exec laravel composer install

# Generate app key
docker-compose exec laravel php artisan key:generate

# Run migrations
docker-compose exec laravel php artisan migrate

# Create admin user (Orchid)
docker-compose exec laravel php artisan orchid:admin admin admin@example.com password

# Start services
docker-compose exec laravel php artisan serve &
docker-compose exec laravel php artisan websockets:serve &
docker-compose exec laravel php artisan horizon &
```

### Option 2: Local Installation

```bash
# Install Laravel
composer create-project laravel/laravel .

# Install packages
composer require orchid/platform
composer require spatie/laravel-permission
composer require laravel/sanctum
composer require mstaack/laravel-postgis
composer require laravel/horizon
composer require predis/predis
composer require beyondcode/laravel-websockets
composer require maatwebsite/excel
composer require spatie/laravel-activitylog

# Configure database in .env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=gps_track
DB_USERNAME=gps_user
DB_PASSWORD=gps_password

# Run migrations
php artisan migrate

# Create admin user
php artisan orchid:admin admin admin@example.com password

# Start services
php artisan serve
php artisan websockets:serve
php artisan horizon
```

## 📊 Database Schema

### Entities (PostgreSQL + PostGIS)

```sql
-- Organizations (Multi-tenancy)
organizations (id, name, slug, settings, created_at, updated_at)

-- Devices
devices (id, organization_id, imei, name, protocol, sim_number, status, settings)

-- Assets
assets (id, organization_id, device_id, name, type, plate_number, odometer, meta)

-- Geofences (PostGIS)
geofences (id, organization_id, name, area:POLYGON, color, alerts_enabled, meta)

-- Alert Rules
alert_rules (id, organization_id, name, type, conditions:JSON, enabled)

-- Alerts
alerts (id, organization_id, device_id, rule_id, type, title, message, severity, seen, payload:JSON)

-- Users & Permissions (Spatie)
users, roles, permissions, model_has_roles, model_has_permissions
```

### Telemetry (TimescaleDB Hypertable)

```sql
positions (
  time TIMESTAMPTZ,
  device_id BIGINT,
  location GEOMETRY(POINT),
  latitude DECIMAL(10,8),
  longitude DECIMAL(11,8),
  speed DECIMAL(5,2),
  heading SMALLINT,
  altitude SMALLINT,
  satellites SMALLINT,
  ignition BOOLEAN,
  fuel_level DECIMAL(5,2),
  battery_voltage DECIMAL(5,2),
  raw_data JSONB
)
```

## 🔌 API Endpoints

### Authentication
```
POST   /api/v1/login
POST   /api/v1/logout
GET    /api/v1/me
POST   /api/v1/register
```

### Devices/Assets
```
GET    /api/v1/devices
POST   /api/v1/devices
GET    /api/v1/devices/{id}
PUT    /api/v1/devices/{id}
DELETE /api/v1/devices/{id}
```

### Positions & Tracking
```
GET    /api/v1/positions/last?device_id={id}
GET    /api/v1/positions?device_id={id}&from={date}&to={date}
GET    /api/v1/trips?device_id={id}&from={date}&to={date}
```

### Geofences
```
GET    /api/v1/geofences
POST   /api/v1/geofences
GET    /api/v1/geofences/{id}
PUT    /api/v1/geofences/{id}
DELETE /api/v1/geofences/{id}
GET    /api/v1/geofences/check?lat={lat}&lng={lng}
```

### Alerts
```
GET    /api/v1/alerts
GET    /api/v1/alerts/{id}
PATCH  /api/v1/alerts/{id}/read
POST   /api/v1/alerts/read-all
DELETE /api/v1/alerts/{id}
```

### Reports
```
POST   /api/v1/reports
GET    /api/v1/reports
GET    /api/v1/reports/{id}
GET    /api/v1/reports/{id}/download
```

### Telemetry
```
GET    /api/v1/telemetry/current/{device}
GET    /api/v1/telemetry/history/{device}?from={date}&to={date}
GET    /api/v1/telemetry/diagnostics/{device}
```

### Statistics
```
GET    /api/v1/statistics/dashboard
GET    /api/v1/statistics/fleet-status
GET    /api/v1/statistics/activity?period={period}
```

Full API documentation: [API.md](./API.md)

## 🔄 Real-Time Updates

### WebSocket Channels

```javascript
// Device position updates
Echo.channel(`device.{deviceId}`)
    .listen('DevicePositionUpdated', (e) => {
        console.log(e.position);
    });

// Organization devices (private channel)
Echo.private(`organization.{orgId}.devices`)
    .listen('DevicePositionUpdated', (e) => {
        console.log(e);
    });

// Alerts (private channel)
Echo.private('alerts')
    .listen('AlertCreated', (e) => {
        console.log(e.alert);
    });
```

## 🔧 Configuration

### Environment Variables

```env
# Application
APP_NAME="GPS Track Backend"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://api.yourdom ain.com

# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=gps_track
DB_USERNAME=gps_user
DB_PASSWORD=

# TimescaleDB
TIMESCALE_HOST=timescale
TIMESCALE_DATABASE=gps_telemetry

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Kafka/NATS
KAFKA_BROKERS=kafka:29092

# WebSockets
WEBSOCKETS_HOST=0.0.0.0
WEBSOCKETS_PORT=6001

# Queue
QUEUE_CONNECTION=redis
HORIZON_BALANCE=simple

# Mail
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls

# SMS/Push Notifications
TWILIO_SID=
TWILIO_AUTH_TOKEN=
FCM_SERVER_KEY=
```

## 📦 Docker Services

```yaml
- laravel      # Laravel application
- horizon      # Queue worker
- websockets   # WebSocket server
- postgres     # PostgreSQL + PostGIS
- timescale    # TimescaleDB
- redis        # Cache & queues
- kafka        # Message bus
- traccar      # GPS ingestion (optional)
- nginx        # Web server
- meilisearch  # Search engine (optional)
```

## 🧪 Testing

```bash
# Run tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# With coverage
php artisan test --coverage
```

## 📈 Performance

### Optimizations

- **TimescaleDB** - Hypertables with compression for telemetry
- **Redis** - Caching last known device states
- **Horizon** - Efficient queue processing
- **Database indexing** - Spatial indexes (GIST), time-based indexes
- **API pagination** - Cursor-based for large datasets
- **Eager loading** - Prevent N+1 queries

### Scaling

- **Horizontal scaling** - Multiple Laravel workers behind load balancer
- **Kafka partitioning** - Distribute GPS data processing
- **Read replicas** - For reporting queries
- **CDN** - Static assets and downloads

## 🔐 Security

- **Sanctum tokens** - API authentication
- **RBAC** - Role-based access control (Spatie)
- **Rate limiting** - API throttling
- **Activity logging** - Audit trail
- **2FA** - Two-factor authentication (optional)
- **Webhook signatures** - HMAC validation
- **Input validation** - Form requests
- **SQL injection protection** - Eloquent ORM

## 📖 Documentation

- [Setup Guide](./SETUP.md)
- [API Documentation](./API.md)
- [Database Schema](./DATABASE.md)
- [Deployment Guide](./DEPLOYMENT.md)
- [Development Guide](./DEVELOPMENT.md)
- [Contributing](./CONTRIBUTING.md)

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](./CONTRIBUTING.md)

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](./LICENSE) file for details.

## 🔗 Related Projects

- [GPS Track Frontend](https://github.com/ridaFD/gps-track) - React frontend
- [Traccar](https://www.traccar.org/) - GPS tracking software

## 💬 Support

For issues and questions:
- GitHub Issues: https://github.com/ridaFD/gps-track-backend/issues
- Email: support@yourcompany.com

---

**Built with Laravel & Orchid Platform**

