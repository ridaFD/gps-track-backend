# 🎛️ Orchid Admin Panel Guide

## 🚀 Quick Access

Your GPS Tracking Admin Panel is now ready!

### 📍 Admin Panel URL
```
http://localhost:8000/admin
```

### 🔐 Default Admin Credentials
- **Email**: `admin@admin.com`
- **Password**: `password`

---

## 📋 What's in the Admin Panel?

### 🗺️ GPS Tracking Section

#### 1. **Dashboard** (`/admin/main`)
- Real-time statistics overview
- Total devices, active devices, alerts
- GPS records counters
- Recent alerts list with severity levels
- Active devices table with last positions
- Quick action links to detailed views

#### 2. **Devices** (`/admin/devices`)
- **List View**: View all GPS tracking devices
  - Filter by type, status, IMEI, name
  - Sort by any column
  - See last known position and speed
  - Status badges (Active, Inactive, Maintenance)
  
- **Create/Edit Device**:
  - Device name and IMEI
  - Type: Car, Truck, Van, Motorcycle, Equipment, Other
  - Status: Active, Inactive, Maintenance
  - Vehicle details: Plate number, model, color, year
  - Driver information: Name and phone
  - Notes field

#### 3. **Geofences** (`/admin/geofences`)
- **List View**: Manage geographical boundaries
  - Filter by type (Circle, Polygon, Rectangle)
  - Active/inactive status
  - Alert on entry/exit indicators
  - Color-coded display
  
- **Create/Edit Geofence**:
  - Geofence name and description
  - Type selection (Circle, Polygon, Rectangle)
  - Coordinates (Latitude, Longitude, Radius for circles)
  - GeoJSON for polygons/rectangles
  - Color picker for map display
  - Alert triggers (on entry/exit)
  - Active status toggle

#### 4. **Alerts** (`/admin/alerts`)
- **List View**: All system alerts
  - Filter by severity (Info, Warning, High, Critical)
  - Filter by type (Geofence entry/exit, Speed limit, etc.)
  - Read/Unread status
  - Device and geofence associations
  - Creation timestamp
  
- **Actions**:
  - Mark individual alerts as read
  - Bulk "Mark All as Read" button
  - View alert details

#### 5. **GPS Positions** (`/admin/positions`)
- View all GPS tracking records
- Device association
- Latitude/Longitude with Google Maps link
- Speed, heading, altitude
- Satellite count
- Fuel level and ignition status
- Device time vs. received time

---

### 🔧 System Management Section

#### 6. **Users** (`/admin/users`)
- Create and manage admin users
- Assign roles and permissions
- User profile management

#### 7. **Roles** (`/admin/roles`)
- Create custom roles
- Assign permissions:
  - **GPS Tracking**: Devices, Geofences, Alerts, Positions
  - **System**: Users, Roles

---

## 🎨 Admin Panel Features

### ✨ Built-in Features
- **🔍 Advanced Filtering**: Every list view has column filters
- **📊 Sorting**: Click any column header to sort
- **📄 Pagination**: Handle large datasets efficiently
- **🎯 Breadcrumbs**: Easy navigation
- **📱 Responsive Design**: Works on mobile, tablet, and desktop
- **🔔 Live Badges**: Unread alert counts on menu items
- **⚡ Real-time Metrics**: Dashboard auto-calculates statistics
- **🎨 Color-coded Status**: Visual indicators for severity and status

### 🔐 Permissions System
Orchid includes a powerful RBAC (Role-Based Access Control) system:

1. **GPS Tracking Permissions**:
   - `platform.devices` - Manage Devices
   - `platform.geofences` - Manage Geofences
   - `platform.alerts` - Manage Alerts
   - `platform.positions` - View GPS Positions

2. **System Permissions**:
   - `platform.systems.users` - Manage Users
   - `platform.systems.roles` - Manage Roles

---

## 📈 Admin Panel Menu Structure

```
🗺️ GPS TRACKING
├── 📊 Dashboard (with unread alerts badge)
├── 🚗 Devices (with active device count)
├── 🗺️ Geofences (with active geofence count)
├── 🔔 Alerts (with unread alerts count)
└── 📍 GPS Positions

SYSTEM MANAGEMENT
├── 👥 Users
└── 🛡️ Roles
```

---

## 🛠️ Customization

### Adding More Fields
You can easily extend any screen by editing:
- **Screens**: `app/Orchid/Screens/`
- **Models**: `app/Models/`
- **Migrations**: `database/migrations/`

### Custom Widgets
Add custom dashboard widgets by creating views in:
```
resources/views/orchid/dashboard/
```

### Changing Theme
Orchid uses Bootstrap 5. Customize in:
```
config/platform.php
```

---

## 🔧 Maintenance Commands

### Create New Admin User
```bash
php artisan orchid:admin
```

### Clear Caches
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Recreate Tables
```bash
php artisan migrate:fresh --seed
```

---

## 📊 Dashboard Metrics Explained

### Device Metrics
- **Total Devices**: All registered GPS devices
- **Active Devices**: Devices with status = 'active'
- **Inactive Devices**: Devices with status = 'inactive'
- **Maintenance**: Devices under maintenance

### Geofence & Alerts
- **Active Geofences**: Enabled geographical boundaries
- **Total Alerts**: All alerts in the system
- **Unread Alerts**: Alerts not yet marked as read
- **Today's Alerts**: Alerts created today

### GPS Records
- **Total GPS Records**: All position records in database
- **Today's GPS Records**: Position records received today

---

## 🎯 Common Tasks

### 1. Add a New Device
1. Navigate to **Devices** → **Create New Device**
2. Fill in device name and IMEI
3. Select device type and status
4. Add vehicle details (optional)
5. Click **Save**

### 2. Create a Geofence
1. Navigate to **Geofences** → **Create New Geofence**
2. Enter geofence name
3. Select type (Circle, Polygon, Rectangle)
4. Input coordinates
5. Enable alert triggers if needed
6. Click **Save**

### 3. Monitor Alerts
1. Navigate to **Alerts**
2. Filter by severity or type
3. Click "Mark as Read" for individual alerts
4. Or use "Mark All as Read" for bulk action

### 4. View Device Location History
1. Navigate to **GPS Positions**
2. Filter by device name
3. Click "View on Map" to see exact location
4. Check timestamps for tracking history

---

## 🚀 Production Deployment

### Security Checklist
- [ ] Change default admin password
- [ ] Set strong `APP_KEY` in `.env`
- [ ] Enable HTTPS in production
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper database backups
- [ ] Set up Redis for better performance
- [ ] Enable rate limiting

### Performance Optimization
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

---

## 📚 Additional Resources

- **Orchid Documentation**: https://orchid.software/en/docs
- **Laravel Documentation**: https://laravel.com/docs
- **GPS Track API**: http://localhost:8000/api/v1
- **API Documentation**: See `routes/api.php`

---

## 🆘 Troubleshooting

### Admin Panel Not Loading?
```bash
php artisan config:clear
php artisan view:clear
php artisan serve
```

### Permission Denied Errors?
```bash
chmod -R 777 storage bootstrap/cache
```

### Database Errors?
```bash
php artisan migrate:fresh --seed
```

### Can't Login?
Create a new admin user:
```bash
php artisan orchid:admin
```

---

## 🎉 You're Ready!

Your Orchid Admin Panel is fully configured and ready to manage your GPS tracking system!

**Happy Tracking! 🚗📍🗺️**

