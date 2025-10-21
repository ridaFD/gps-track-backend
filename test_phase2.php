<?php
// Test Phase 2 Features
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🚀 TESTING PHASE 2 FEATURES\n";
echo str_repeat("━", 60) . "\n\n";

use App\Jobs\ProcessPositionJob;
use App\Jobs\GenerateReportJob;
use App\Models\Position;
use App\Models\Alert;
use App\Models\Device;

// 1. Test Position Processing
echo "1️⃣  Testing Position Processing...\n";
ProcessPositionJob::dispatch([
    'latitude' => 40.7589,
    'longitude' => -73.9851,
    'speed' => 45,
    'heading' => 180,
    'altitude' => 10,
    'ignition' => true,
    'fuel_level' => 75,
    'device_time' => now(),
], 1);
echo "✅ Normal position dispatched\n\n";

// 2. Test Speed Alert (>120 km/h)
echo "2️⃣  Testing Speed Alert...\n";
ProcessPositionJob::dispatch([
    'latitude' => 40.7589,
    'longitude' => -73.9851,
    'speed' => 160, // Will trigger alert!
    'heading' => 180,
    'ignition' => true,
    'device_time' => now(),
], 1);
echo "⚠️  Speed alert job dispatched (160 km/h)\n\n";

// 3. Test Low Battery Alert
echo "3️⃣  Testing Low Battery Alert...\n";
ProcessPositionJob::dispatch([
    'latitude' => 40.7589,
    'longitude' => -73.9851,
    'speed' => 30,
    'battery_level' => 15, // Will trigger alert!
    'device_time' => now(),
], 1);
echo "🔋 Low battery alert job dispatched (15%)\n\n";

// 4. Test Report Generation
echo "4️⃣  Testing Report Generation...\n";
GenerateReportJob::dispatch('devices');
echo "📊 Devices report queued\n";
GenerateReportJob::dispatch('trips', ['device_id' => 1]);
echo "📊 Trips report queued\n";
GenerateReportJob::dispatch('alerts');
echo "📊 Alerts report queued\n\n";

echo "⏳ Waiting for jobs to process (5 seconds)...\n";
sleep(5);

// 5. Check Results
echo "\n5️⃣  Checking Results...\n";
echo str_repeat("━", 60) . "\n\n";

$positionCount = Position::count();
$alertCount = Alert::count();
$deviceCount = Device::count();

echo "📍 Total GPS Positions: {$positionCount}\n";
echo "🚨 Total Alerts: {$alertCount}\n";
echo "🚗 Total Devices: {$deviceCount}\n\n";

// Latest positions
echo "📍 Latest 3 Positions:\n";
$positions = Position::with('device')->latest('device_time')->take(3)->get();
foreach($positions as $pos) {
    $device = $pos->device ? $pos->device->name : 'Unknown';
    echo "   - Device: {$device} | Speed: {$pos->speed} km/h | Time: " . $pos->device_time->format('H:i:s') . "\n";
}

// Latest alerts
echo "\n🚨 Latest 5 Alerts:\n";
$alerts = Alert::with('device')->latest()->take(5)->get();
if ($alerts->count() > 0) {
    foreach($alerts as $alert) {
        $device = $alert->device ? $alert->device->name : 'Unknown';
        echo "   - [" . strtoupper($alert->severity) . "] {$alert->type}: {$alert->message}\n";
    }
} else {
    echo "   (No alerts yet)\n";
}

// Check reports
echo "\n📊 Generated Reports:\n";
$reports = \Storage::files('reports');
if (count($reports) > 0) {
    foreach(array_slice($reports, -3) as $report) {
        $filename = basename($report);
        $size = round(\Storage::size($report) / 1024, 2);
        echo "   - {$filename} ({$size} KB)\n";
    }
} else {
    echo "   (No reports yet - check queue processing)\n";
}

echo "\n✅ ALL TESTS COMPLETE!\n";
echo str_repeat("━", 60) . "\n";
echo "\n📊 Check Horizon Dashboard: http://localhost:8000/horizon\n";
echo "🎛️  Check Admin Panel: http://localhost:8000/admin\n";
echo "📁 Reports folder: " . storage_path('app/reports') . "\n\n";

