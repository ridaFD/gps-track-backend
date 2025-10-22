#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Events\DevicePositionUpdated;
use App\Models\Position;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                                                               ║\n";
echo "║           🔴 PUSHER CONNECTION TEST (LIVE)                    ║\n";
echo "║                                                               ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Check configuration
echo "📋 Checking Pusher Configuration...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$config = [
    'BROADCAST_DRIVER' => env('BROADCAST_DRIVER'),
    'PUSHER_APP_ID' => env('PUSHER_APP_ID'),
    'PUSHER_APP_KEY' => env('PUSHER_APP_KEY'),
    'PUSHER_APP_SECRET' => env('PUSHER_APP_SECRET') ? '***HIDDEN***' : null,
    'PUSHER_APP_CLUSTER' => env('PUSHER_APP_CLUSTER'),
    'PUSHER_HOST' => env('PUSHER_HOST') ?: '(default - pusher.com)',
    'PUSHER_PORT' => env('PUSHER_PORT') ?: '443',
    'PUSHER_SCHEME' => env('PUSHER_SCHEME') ?: 'https',
];

foreach ($config as $key => $value) {
    $status = $value ? '✅' : '❌';
    echo "$status $key: " . ($value ?: 'NOT SET') . "\n";
}

echo "\n";

// Check if all required values are set
if (env('BROADCAST_DRIVER') !== 'pusher') {
    echo "❌ ERROR: BROADCAST_DRIVER is not set to 'pusher'\n";
    exit(1);
}

if (!env('PUSHER_APP_KEY')) {
    echo "❌ ERROR: PUSHER_APP_KEY is not set\n";
    exit(1);
}

echo "✅ Configuration looks good!\n\n";

// Test Pusher connection
echo "🔌 Testing Pusher Connection...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    // Create a test position
    $testPosition = new Position([
        'device_id' => 1,
        'latitude' => 40.7128 + (rand(-100, 100) / 10000),
        'longitude' => -74.0060 + (rand(-100, 100) / 10000),
        'speed' => rand(30, 80),
        'altitude' => rand(5, 50),
        'heading' => rand(0, 360),
        'device_time' => now(),
        'server_time' => now(),
    ]);

    echo "📡 Broadcasting test event to Pusher...\n";
    echo "   Event: DevicePositionUpdated\n";
    echo "   Channel: devices\n";
    echo "   Device ID: 1\n";
    echo "   Location: {$testPosition->latitude}, {$testPosition->longitude}\n";
    echo "   Speed: {$testPosition->speed} km/h\n";
    echo "\n";

    // Broadcast the event
    broadcast(new DevicePositionUpdated($testPosition));

    echo "✅ Event broadcasted successfully!\n\n";

    echo "🎯 Verification Steps:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. Open Pusher Dashboard: https://dashboard.pusher.com/\n";
    echo "2. Select your app: 'spiffy-leaf-845'\n";
    echo "3. Go to 'Debug Console' tab\n";
    echo "4. You should see the event that was just sent!\n";
    echo "\n";
    echo "Event Details:\n";
    echo "  • Channel: devices\n";
    echo "  • Event: App\\Events\\DevicePositionUpdated\n";
    echo "  • Time: " . now()->format('H:i:s') . "\n";
    echo "\n";
    echo "If you see the event in Pusher Debug Console:\n";
    echo "   ✅ Backend → Pusher connection is WORKING!\n";
    echo "\n";

} catch (\Exception $e) {
    echo "❌ ERROR: Failed to broadcast event\n";
    echo "   Message: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";
    echo "\n";
    echo "💡 Troubleshooting:\n";
    echo "   1. Check your Pusher credentials in .env\n";
    echo "   2. Run: php artisan config:clear\n";
    echo "   3. Make sure cluster is correct (ap2)\n";
    echo "   4. Check your internet connection\n";
    exit(1);
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🎉 Test Complete!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";

