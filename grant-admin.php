#!/usr/bin/env php
<?php

/**
 * Grant Orchid Admin Permissions
 * Run: php grant-admin.php email@example.com
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Orchid\Support\Facades\Dashboard;

// Get email from command line argument
$email = $argv[1] ?? 'admin@admin.com';

echo "\n";
echo "🔐 Granting Orchid Admin Permissions\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Email: $email\n\n";

// Find user
$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ User not found: $email\n";
    echo "\nAvailable users:\n";
    User::all()->each(function($u) {
        echo "  - {$u->email}\n";
    });
    exit(1);
}

echo "✅ User found: {$user->name} ({$user->email})\n\n";

// Define all Orchid permissions (from PlatformProvider.php)
$permissionKeys = [
    // GPS Tracking
    'platform.devices',
    'platform.geofences',
    'platform.alerts',
    'platform.positions',
    // System
    'platform.systems.roles',
    'platform.systems.users',
    // Orchid default permissions
    'platform.index',
];

echo "📋 Granting permissions:\n";
foreach ($permissionKeys as $permission) {
    echo "  ✅ {$permission}\n";
}
echo "\n";

// Grant all permissions
$user->permissions = $permissionKeys;
$user->save();

echo "✅ Granted all permissions to {$user->email}\n\n";

echo "🎉 SUCCESS!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "User: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Permissions: " . count($permissionKeys) . " permissions granted\n";
echo "\nYou can now access: http://localhost:8000/admin\n\n";

