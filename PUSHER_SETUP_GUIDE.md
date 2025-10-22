# 🔴 Pusher Setup Guide - Real-Time Maps

**Goal:** Enable live GPS tracking with WebSockets  
**Time:** ~1 hour  
**Status:** Step-by-step instructions

---

## 📋 **What You'll Get**

After completing this setup:
- ✅ Live device position updates on the map
- ✅ Real-time alerts and notifications
- ✅ Device status changes (online/offline)
- ✅ No page refresh needed
- ✅ Professional real-time tracking

---

## 🚀 **Step 1: Sign Up for Pusher (5 min)**

### **Option A: Pusher Cloud (Recommended - Free Tier)**

1. **Go to:** https://pusher.com/
2. **Click:** "Sign Up" (top right)
3. **Sign up with:**
   - GitHub account (easiest)
   - OR Google account
   - OR Email

4. **After signing up:**
   - Click "Create app" or "Channels" in the dashboard
   - Choose "Channels" (not Beams)

5. **App Configuration:**
   - **App Name:** GPS-Track-{YourName} (e.g., "GPS-Track-Rida")
   - **Cluster:** Choose closest to you:
     - `us2` - US East (Virginia)
     - `us3` - US West (Oregon)
     - `eu` - Europe (Ireland)
     - `ap1` - Asia Pacific (Singapore)
   - **Tech Stack:** 
     - Frontend: React
     - Backend: Laravel
   - Click "Create App"

6. **Get Your Credentials:**
   After creating the app, you'll see:
   - **App ID:** (6-digit number)
   - **Key:** (20-character string)
   - **Secret:** (20-character string)
   - **Cluster:** (e.g., us2, eu, ap1)

   📋 **COPY THESE NOW!** You'll need them in Step 2.

---

### **Option B: Soketi (Local - Free Forever)**

If you prefer to run everything locally:

```bash
# Install Soketi globally
npm install -g @soketi/soketi

# Run Soketi (keep this terminal open)
soketi start
```

**Soketi Credentials (use these if going local):**
```
PUSHER_APP_ID=app-id
PUSHER_APP_KEY=app-key
PUSHER_APP_SECRET=app-secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1
```

---

## ⚙️ **Step 2: Update Backend .env (2 min)**

**Location:** `/Users/ridafakherlden/www/gps-track-backend/.env`

### **For Pusher Cloud:**

Find these lines in your `.env` file and update them:

```env
BROADCAST_DRIVER=pusher

# Replace with YOUR credentials from Pusher dashboard
PUSHER_APP_ID=123456
PUSHER_APP_KEY=your_app_key_here
PUSHER_APP_SECRET=your_app_secret_here
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=us2  # or eu, ap1, etc.
```

### **For Soketi (Local):**

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=app-id
PUSHER_APP_KEY=app-key
PUSHER_APP_SECRET=app-secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1
```

**After updating:**
```bash
cd /Users/ridafakherlden/www/gps-track-backend
php artisan config:clear
php artisan cache:clear
```

---

## 🎨 **Step 3: Update Frontend .env (2 min)**

**Location:** `/Users/ridafakherlden/www/gps-track/.env`

Create this file if it doesn't exist:

### **For Pusher Cloud:**

```env
REACT_APP_API_URL=http://localhost:8000/api/v1
REACT_APP_PUSHER_KEY=your_app_key_here
REACT_APP_PUSHER_CLUSTER=us2
REACT_APP_PUSHER_HOST=
REACT_APP_PUSHER_PORT=443
REACT_APP_PUSHER_SCHEME=https
```

### **For Soketi (Local):**

```env
REACT_APP_API_URL=http://localhost:8000/api/v1
REACT_APP_PUSHER_KEY=app-key
REACT_APP_PUSHER_CLUSTER=mt1
REACT_APP_PUSHER_HOST=127.0.0.1
REACT_APP_PUSHER_PORT=6001
REACT_APP_PUSHER_SCHEME=http
```

**Restart your frontend:**
```bash
# Stop the running frontend (Ctrl+C)
# Then restart:
npm start
```

---

## 🧪 **Step 4: Test WebSocket Connection (10 min)**

### **Test 1: Check Pusher Dashboard**

1. Go to your Pusher app dashboard
2. Click "Debug Console" tab
3. You should see "Connection opened" messages when your app loads
4. Leave this tab open to monitor events

### **Test 2: Trigger a Test Event (Backend)**

```bash
cd /Users/ridafakherlden/www/gps-track-backend
php artisan tinker
```

In tinker, run:
```php
use App\Events\DevicePositionUpdated;
use App\Models\Position;

// Create a test position
$position = new Position([
    'device_id' => 1,
    'latitude' => 40.7128,
    'longitude' => -74.0060,
    'speed' => 55.5,
    'altitude' => 10,
    'heading' => 90,
    'device_time' => now(),
    'server_time' => now(),
]);

// Broadcast it
broadcast(new DevicePositionUpdated($position));

// You should see this in Pusher Debug Console!
```

### **Test 3: Check Frontend Console**

1. Open your frontend: http://localhost:3000
2. Open Browser DevTools (F12)
3. Go to Console tab
4. You should see:
   - "Pusher connected"
   - "Subscribed to channel: devices"
   - No connection errors

---

## 🗺️ **Step 5: Enable Real-Time Updates in Frontend (20 min)**

### **Update websocket.js:**

The file already exists but needs the Pusher credentials from `.env`:

**Location:** `/Users/ridafakherlden/www/gps-track/src/services/websocket.js`

Update it:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
  broadcaster: 'pusher',
  key: process.env.REACT_APP_PUSHER_KEY,
  cluster: process.env.REACT_APP_PUSHER_CLUSTER,
  wsHost: process.env.REACT_APP_PUSHER_HOST || undefined,
  wsPort: process.env.REACT_APP_PUSHER_PORT || 443,
  wssPort: process.env.REACT_APP_PUSHER_PORT || 443,
  forceTLS: process.env.REACT_APP_PUSHER_SCHEME === 'https',
  encrypted: true,
  disableStats: true,
  enabledTransports: ['ws', 'wss'],
});

export default echo;
```

### **Use in Components:**

Example for Dashboard.js:

```javascript
import echo from '../services/websocket';

useEffect(() => {
  // Listen for device position updates
  echo.channel('devices')
    .listen('.position.updated', (data) => {
      console.log('New position:', data);
      // Update your map marker here
      updateDevicePosition(data.device_id, data.position);
    });

  // Listen for alerts
  echo.channel('alerts')
    .listen('.alert.created', (data) => {
      console.log('New alert:', data);
      // Show notification
      showNotification(data.alert);
    });

  // Cleanup
  return () => {
    echo.leaveChannel('devices');
    echo.leaveChannel('alerts');
  };
}, []);
```

---

## ✅ **Step 6: Verify Everything Works (15 min)**

### **Checklist:**

```
□ Pusher account created
□ Backend .env updated with credentials
□ Frontend .env updated with credentials
□ Backend restarted (php artisan serve)
□ Frontend restarted (npm start)
□ Pusher Debug Console shows connections
□ Browser console shows "Pusher connected"
□ Test event appears in Pusher dashboard
□ No errors in backend logs
□ No errors in frontend console
```

### **Test Real-Time Updates:**

**Method 1: Via API**
```bash
# Send a test position update
curl -X POST http://localhost:8000/api/v1/positions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "device_id": 1,
    "latitude": 40.7128,
    "longitude": -74.0060,
    "speed": 55.5,
    "altitude": 10,
    "heading": 90
  }'

# Check Pusher Debug Console - should see the event!
# Check Frontend Console - should see the update!
```

**Method 2: Via Database**
```bash
php artisan tinker

# Trigger position job
$device = App\Models\Device::first();
$position = new App\Models\Position([
    'device_id' => $device->id,
    'latitude' => 40.7128 + rand(-100, 100) / 10000,
    'longitude' => -74.0060 + rand(-100, 100) / 10000,
    'speed' => rand(0, 100),
    'altitude' => 10,
    'heading' => rand(0, 360),
    'device_time' => now(),
    'server_time' => now(),
]);

dispatch(new App\Jobs\ProcessPositionJob($position));
```

---

## 🐛 **Troubleshooting**

### **Error: "Unable to connect to Pusher"**

**Solution:**
1. Check your credentials in `.env`
2. Make sure cluster is correct (us2, eu, ap1)
3. Clear Laravel config: `php artisan config:clear`
4. Restart both backend and frontend

### **Error: "CORS policy blocking"**

**Solution:**
Update `config/cors.php`:
```php
'paths' => ['api/*', 'broadcasting/auth'],
'allowed_origins' => ['http://localhost:3000'],
```

### **Error: "Channel not found"**

**Solution:**
Make sure your events implement `ShouldBroadcast`:
```php
class DevicePositionUpdated implements ShouldBroadcast
```

### **Error: "Authentication failed"**

**Solution:**
For private channels, you need to authenticate. Check `routes/channels.php`.

---

## 📊 **Pusher Dashboard Monitoring**

### **What to Watch:**

1. **Connections:** Number of active WebSocket connections
2. **Messages:** Number of events broadcasted
3. **Debug Console:** See events in real-time
4. **Stats:** Connection history and usage

### **Free Tier Limits:**

- **Connections:** 100 concurrent
- **Messages:** 200K per day
- **Channels:** Unlimited
- **History:** 24 hours

This is more than enough for development and testing!

---

## 🎯 **Expected Behavior After Setup**

### **What Should Happen:**

1. **Frontend loads:**
   - Connects to Pusher
   - Subscribes to channels
   - Console shows "Pusher connected"

2. **Backend sends position:**
   - Processes GPS data
   - Broadcasts to Pusher
   - Pusher dashboard shows event

3. **Frontend receives:**
   - Gets position update
   - Updates map marker in real-time
   - No page refresh needed!

4. **Users see:**
   - Devices moving on map live
   - Instant alert notifications
   - Status changes immediately

---

## 🚀 **Next Steps After Pusher Works**

Once real-time updates are working:

1. **Add to Dashboard:** Real-time device list updates
2. **Add to Map:** Live marker movement
3. **Add to Alerts:** Instant notification popup
4. **Add to Devices:** Online/offline status indicator
5. **Add Status Badges:** Show connection quality

---

## 💰 **Pusher Pricing (Optional Upgrade)**

**Free Tier:**
- 100 concurrent connections
- 200K messages/day
- Perfect for development

**Paid Plans (when you scale):**
- **Startup ($49/mo):** 500 connections, 10M messages
- **Business ($299/mo):** 2,000 connections, 100M messages
- **Enterprise:** Custom pricing

Start with free tier - it's plenty for testing!

---

## ✅ **Success Checklist**

When everything is working, you should see:

- [x] Pusher account created
- [x] Credentials added to backend `.env`
- [x] Credentials added to frontend `.env`
- [x] Pusher dashboard shows connections
- [x] Test events broadcast successfully
- [x] Frontend receives events
- [x] No errors in consoles
- [x] Real-time updates working!

---

## 🎊 **You Did It!**

Your GPS tracking platform now has **real-time updates**!

This is a HUGE feature that makes your platform feel professional and alive!

**What's next?**
- Test with multiple devices
- Add real-time map updates
- Add instant alert notifications
- Show off to stakeholders! 🚀

---

**Status:** Ready for real-time tracking! 🔴🟢

