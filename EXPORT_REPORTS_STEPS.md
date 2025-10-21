# 📥 How to Export Reports - Step by Step

## ✨ Method 1: Using the Frontend (React App)

### **Prerequisites:**
Make sure both servers are running:

```bash
# Terminal 1: Backend + Queue Worker
cd /Users/ridafakherlden/www/gps-track-backend
php artisan serve
# Keep this running!

# Terminal 2: Queue Worker (IMPORTANT!)
cd /Users/ridafakherlden/www/gps-track-backend
php artisan queue:work
# Keep this running!

# Terminal 3: Frontend
cd /Users/ridafakherlden/www/gps-track
npm start
# Opens browser automatically
```

---

### **Step 1: Open Reports Page**
```
http://localhost:3000/reports
```

---

### **Step 2: Download Existing Reports**

You'll see a table like this:

```
┌─────────────────────────────────────┬────────┬──────────────────────┬──────────┐
│ Report Name                         │ Size   │ Generated            │ Actions  │
├─────────────────────────────────────┼────────┼──────────────────────┼──────────┤
│ alerts_2025-10-21_225608.xlsx      │ 6.72 KB│ 10/21/2025, 6:56 PM  │[Download]│
│ devices_2025-10-21_225608.xlsx     │ 7.00 KB│ 10/21/2025, 6:56 PM  │[Download]│
│ trips_2025-10-21_225608.xlsx       │ 7.24 KB│ 10/21/2025, 6:56 PM  │[Download]│
└─────────────────────────────────────┴────────┴──────────────────────┴──────────┘
```

**Action:** Click any **[Download]** button → Excel file downloads to your Downloads folder! 🎉

---

### **Step 3: Generate a NEW Report** (Optional)

1. **Click** the blue **"Generate Report"** button (top-right corner)

2. **Wait** 2-3 seconds

3. **See** success notification:
   ```
   ✅ Devices report is being generated! 
      It will be ready for download shortly.
   ```

4. **After 3 seconds**, the table **auto-refreshes** with the new report

5. **Click [Download]** on the new report

---

## 🖥️ Method 2: Using Command Line (Direct Download)

### **Step 1: List Available Reports**
```bash
cd /Users/ridafakherlden/www/gps-track-backend
ls -lh storage/app/reports/
```

**Output:**
```
-rw-r--r--  1 ridafakherlden  staff   6.7K Oct 22 01:56 alerts_2025-10-21_225608.xlsx
-rw-r--r--  1 ridafakherlden  staff   7.0K Oct 22 01:56 devices_2025-10-21_225608.xlsx
-rw-r--r--  1 ridafakherlden  staff   7.2K Oct 22 01:56 trips_2025-10-21_225608.xlsx
```

---

### **Step 2: Copy Reports to Your Desktop**
```bash
# Copy all reports to Desktop
cp storage/app/reports/*.xlsx ~/Desktop/

# Or copy a specific report
cp storage/app/reports/devices_2025-10-21_225608.xlsx ~/Desktop/
```

---

### **Step 3: Open in Excel**
```bash
# MacOS - Open with default app
open ~/Desktop/devices_2025-10-21_225608.xlsx

# Or open folder in Finder
open ~/Desktop/
```

---

## 🌐 Method 3: Using API (cURL)

### **Step 1: List Available Reports**
```bash
curl http://localhost:8000/api/v1/reports | jq
```

**Response:**
```json
{
  "reports": [
    {
      "filename": "alerts_2025-10-21_225608.xlsx",
      "size": 6877,
      "created_at": 1729549568,
      "download_url": "/api/v1/reports/download/alerts_2025-10-21_225608.xlsx"
    },
    {
      "filename": "devices_2025-10-21_225608.xlsx",
      "size": 7168,
      "created_at": 1729549568,
      "download_url": "/api/v1/reports/download/devices_2025-10-21_225608.xlsx"
    }
  ],
  "count": 3
}
```

---

### **Step 2: Download a Report**
```bash
# Download to current directory
curl -O http://localhost:8000/api/v1/reports/download/devices_2025-10-21_225608.xlsx

# Or save with custom name
curl http://localhost:8000/api/v1/reports/download/devices_2025-10-21_225608.xlsx \
  -o my-devices-report.xlsx
```

---

### **Step 3: Open Downloaded File**
```bash
open devices_2025-10-21_225608.xlsx
```

---

## 🆕 Generate New Reports (All Methods)

### **Via Frontend:**
1. Go to http://localhost:3000/reports
2. Click "Generate Report" button
3. Wait 3 seconds
4. New report appears → Click Download

---

### **Via API:**
```bash
# Generate Devices Report
curl -X POST http://localhost:8000/api/v1/reports/generate \
  -H "Content-Type: application/json" \
  -d '{"type": "devices"}'

# Generate Trips Report
curl -X POST http://localhost:8000/api/v1/reports/generate \
  -H "Content-Type: application/json" \
  -d '{"type": "trips"}'

# Generate Alerts Report
curl -X POST http://localhost:8000/api/v1/reports/generate \
  -H "Content-Type: application/json" \
  -d '{"type": "alerts"}'

# Wait 5 seconds for processing
sleep 5

# List and download
curl http://localhost:8000/api/v1/reports | jq
curl -O http://localhost:8000/api/v1/reports/download/devices_2025-10-21_NEWTIME.xlsx
```

---

### **Via Laravel Tinker:**
```bash
cd /Users/ridafakherlden/www/gps-track-backend
php artisan tinker
```

```php
// Generate all 3 types
App\Jobs\GenerateReportJob::dispatch('devices');
App\Jobs\GenerateReportJob::dispatch('trips');
App\Jobs\GenerateReportJob::dispatch('alerts');

// Process queue
\Artisan::call('queue:work --stop-when-empty');

// List reports
$files = Storage::files('reports');
foreach($files as $file) {
    echo basename($file) . "\n";
}
```

---

## 📊 What's In Each Report?

### **Devices Report** (devices_*.xlsx)
Contains all your GPS devices with:
- Device ID, Name, IMEI
- Type (car, truck, van, motorcycle)
- Status (active, inactive, maintenance)
- Plate Number, Model, Color, Year
- Driver Name & Phone
- Last GPS Position (lat, lng)
- Last Speed & Update Time

---

### **Trips Report** (trips_*.xlsx)
Contains calculated trips from GPS positions:
- Device Name
- Start & End Times
- Start & End Locations
- Distance Traveled (km)
- Trip Duration (hours)
- Average & Max Speed
- Idle Time
- Fuel Consumed (if available)

---

### **Alerts Report** (alerts_*.xlsx)
Contains all system alerts:
- Device Name
- Alert Type (speed_limit, geofence_entry, battery_low, etc.)
- Severity (info, warning, high, critical)
- Alert Message
- Created Time
- Read Status

---

## 🎨 Quick One-Liner Commands

```bash
# Copy all reports to Desktop
cp /Users/ridafakherlden/www/gps-track-backend/storage/app/reports/*.xlsx ~/Desktop/ && open ~/Desktop/

# Generate all 3 types via API
for type in devices trips alerts; do 
  curl -X POST http://localhost:8000/api/v1/reports/generate \
    -H "Content-Type: application/json" \
    -d "{\"type\": \"$type\"}"
done

# Download all reports
cd ~/Desktop/ && \
for report in $(curl -s http://localhost:8000/api/v1/reports | jq -r '.reports[].filename'); do 
  curl -O http://localhost:8000/api/v1/reports/download/$report
done && open .
```

---

## ✅ Checklist: Before Exporting

- [ ] Backend server running (php artisan serve)
- [ ] Queue worker running (php artisan queue:work) ⚠️ **IMPORTANT!**
- [ ] Frontend running (npm start) - if using UI
- [ ] Reports exist in storage/app/reports/
- [ ] Browser at http://localhost:3000/reports

---

## 🐛 Troubleshooting

**Problem:** "No reports available"
```bash
# Check if reports exist
ls -la /Users/ridafakherlden/www/gps-track-backend/storage/app/reports/

# If empty, generate new ones:
cd /Users/ridafakherlden/www/gps-track-backend
php test_phase2.php
```

**Problem:** "Download not working"
```bash
# Check queue worker is running
ps aux | grep "queue:work"

# If not running, start it:
cd /Users/ridafakherlden/www/gps-track-backend
php artisan queue:work
```

**Problem:** "404 Not Found"
```bash
# Make sure backend is running on port 8000
curl http://localhost:8000/api/v1/health
# Should return: {"status":"ok"}
```

---

## 🎉 That's It!

**Recommended:** Use Method 1 (Frontend UI) - it's the easiest! ✨

**Current Reports Ready:**
- ✅ alerts_2025-10-21_225608.xlsx (6.7 KB)
- ✅ devices_2025-10-21_225608.xlsx (7.0 KB)  
- ✅ trips_2025-10-21_225608.xlsx (7.2 KB)

Just go to http://localhost:3000/reports and click Download! 🚀
