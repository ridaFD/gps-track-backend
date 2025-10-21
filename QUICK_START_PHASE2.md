# ⚡ Quick Start: Phase 2 Real-time Features

## 🚀 Get Started in 5 Minutes!

### **Step 1: Start Redis** (Required for Queues)
```bash
# MacOS
brew services start redis

# Check if running
redis-cli ping
# Should return: PONG
```

---

### **Step 2: Start Horizon** (Queue Worker)
```bash
cd /Users/ridafakherlden/www/gps-track-backend
php artisan horizon
```

**Keep this running!** Open a new terminal for other commands.

**Monitor Dashboard:** http://localhost:8000/horizon

---

### **Step 3: Test Position Processing**
```bash
# In a new terminal
cd /Users/ridafakherlden/www/gps-track-backend
php artisan tinker
```

```php
// Test processing a GPS position
App\Jobs\ProcessPositionJob::dispatch([
    'latitude' => 40.7589,
    'longitude' => -73.9851,
    'speed' => 150, // Will trigger speed alert!
    'heading' => 180,
    'ignition' => true,
    'fuel_level' => 75,
    'device_time' => now(),
], 1); // Device ID 1

// Check if it worked
App\Models\Position::latest()->first();

// Check if alert was created (speed > 120)
App\Models\Alert::latest()->first();
```

---

### **Step 4: Generate a Report**
```bash
php artisan tinker
```

```php
// Generate devices report
App\Jobs\GenerateReportJob::dispatch('devices');

// Generate trips report for device 1
App\Jobs\GenerateReportJob::dispatch('trips', [
    'device_id' => 1,
    'from' => now()->subDays(7),
    'to' => now(),
]);

// Generate alerts report
App\Jobs\GenerateReportJob::dispatch('alerts', [
    'severity' => 'high',
]);

// Check generated files
exit
ls -lh storage/app/reports/
```

---

### **Step 5: Test Real-time Broadcasting**
```bash
php artisan tinker
```

```php
// Broadcast a position update
$position = App\Models\Position::first();
broadcast(new App\Events\DevicePositionUpdated($position));

// Broadcast an alert
$alert = App\Models\Alert::first();
broadcast(new App\Events\AlertCreated($alert));
```

**Note:** You'll see these in Horizon dashboard under "Recent Jobs"

---

## 📊 What You Just Set Up

### ✅ **Background Processing**
- GPS positions processed asynchronously
- Alerts evaluated automatically
- Reports generated in background
- No blocking requests!

### ✅ **Real-time Broadcasting**
- Position updates broadcast to `devices` channel
- Alerts broadcast to `alerts` channel
- Frontend can subscribe and update live

### ✅ **Automatic Alerts**
- **Speed Limit**: > 120 km/h → HIGH alert
- **Geofence Entry/Exit**: When device crosses boundary
- **Idle Detection**: Engine on, speed 0 for 30+ min
- **Low Battery**: < 20% → HIGH alert

### ✅ **Excel Reports**
- **Devices**: All devices with last positions
- **Trips**: Position history
- **Alerts**: Alert history
- Files saved in: `storage/app/reports/`

---

## 🎯 Next Steps

### **1. Integrate with Frontend**
Update React frontend to listen to WebSocket events:

```javascript
// Install packages
npm install pusher-js laravel-echo

// src/services/websocket.js (already exists)
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'pusher',
    key: 'local',
    wsHost: '127.0.0.1',
    wsPort: 6001,
    forceTLS: false,
});

// Listen to position updates
echo.channel('devices')
    .listen('.position.updated', (e) => {
        console.log('Position updated:', e);
        // Update map marker
    });

// Listen to alerts
echo.channel('alerts')
    .listen('.alert.created', (e) => {
        console.log('New alert:', e);
        // Show notification
    });
```

### **2. Create API Endpoints for Reports**
Add to `routes/api.php`:

```php
// Generate report
Route::post('/reports/generate', function (Request $request) {
    $type = $request->input('type'); // devices, trips, alerts
    $params = $request->input('params', []);
    
    GenerateReportJob::dispatch($type, $params);
    
    return response()->json([
        'message' => 'Report generation started',
        'type' => $type,
    ]);
});

// Download report
Route::get('/reports/download/{filename}', function ($filename) {
    $path = storage_path("app/reports/{$filename}");
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->download($path);
});
```

### **3. Add Position Ingestion Endpoint**
Add to `routes/api.php`:

```php
// Ingest GPS position from device
Route::post('/positions/ingest', function (Request $request) {
    $validated = $request->validate([
        'device_id' => 'required|integer|exists:devices,id',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'speed' => 'nullable|numeric',
        'heading' => 'nullable|integer',
        'ignition' => 'nullable|boolean',
    ]);
    
    ProcessPositionJob::dispatch($validated, $validated['device_id']);
    
    return response()->json(['message' => 'Position received']);
});
```

---

## 🛠️ Troubleshooting

### **Horizon not starting?**
```bash
# Clear config
php artisan config:clear

# Ensure Redis is running
redis-cli ping

# Check .env
grep QUEUE_CONNECTION .env
# Should be: QUEUE_CONNECTION=redis
```

### **Jobs not processing?**
```bash
# Check Horizon dashboard
open http://localhost:8000/horizon

# Check failed jobs
php artisan horizon:list

# Clear failed jobs
php artisan horizon:clear

# Restart Horizon
php artisan horizon:terminate
php artisan horizon
```

### **Broadcasting not working?**
```bash
# Check .env
grep BROADCAST_DRIVER .env
# Should be: BROADCAST_DRIVER=pusher

# Test manually
php artisan tinker
>>> broadcast(new App\Events\AlertCreated(App\Models\Alert::first()));
```

---

## 📚 Documentation

- **Full Guide:** `PHASE2_REALTIME_FEATURES.md` (16KB)
- **Horizon Docs:** https://laravel.com/docs/10.x/horizon
- **Broadcasting Docs:** https://laravel.com/docs/10.x/broadcasting
- **Excel Docs:** https://docs.laravel-excel.com/

---

## ✅ **You're All Set!**

Your GPS tracking system now has:
- ✅ **Background Processing** (Horizon + Redis)
- ✅ **Real-time Updates** (WebSocket Broadcasting)
- ✅ **Automatic Alerts** (4 types)
- ✅ **Excel Reports** (3 types)
- ✅ **Activity Logging** (Audit trail)

**Ready for production! 🎉**

---

## 🔥 Quick Commands Reference

```bash
# Start everything
brew services start redis
php artisan horizon
php artisan serve

# Monitor
open http://localhost:8000/horizon
open http://localhost:8000/admin

# Test
php artisan tinker
>>> App\Jobs\ProcessPositionJob::dispatch([...], 1);
>>> App\Jobs\GenerateReportJob::dispatch('devices');

# Check reports
ls -lh storage/app/reports/

# View logs
tail -f storage/logs/laravel.log
```

**Happy Tracking! 🚗📍🗺️⚡**

