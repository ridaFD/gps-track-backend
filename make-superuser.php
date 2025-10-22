#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Orchid\Support\Facades\Dashboard;

$email = $argv[1] ?? 'admin@admin.com';

echo "\n";
echo "🔐 Making Superuser (Orchid Admin)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Email: $email\n\n";

$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ User not found!\n\n";
    exit(1);
}

echo "✅ User found: {$user->name}\n\n";

// Method 1: Set all permissions to TRUE (wildcard)
echo "📋 Setting superuser permissions...\n";

// Get all available permissions in Orchid format (key => boolean)
$allPermissions = [
    'platform.index' => true,
    'platform.systems.roles' => true,
    'platform.systems.users' => true,
    'platform.systems.attachment' => true,
    'platform.devices' => true,
    'platform.geofences' => true,
    'platform.alerts' => true,
    'platform.positions' => true,
];

$user->permissions = $allPermissions;
$user->save();

echo "✅ Granted " . count($allPermissions) . " permissions (Orchid format)\n\n";

// Also verify user can access platform
echo "🔍 Verifying access...\n";
echo "   User ID: {$user->id}\n";
echo "   Email: {$user->email}\n";
echo "   Permissions: " . json_encode($user->permissions, JSON_PRETTY_PRINT) . "\n";

echo "\n🎉 SUCCESS!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "User '{$user->email}' is now a superuser!\n";
echo "\nNext steps:\n";
echo "1. Clear your browser cache (or use Incognito)\n";
echo "2. Go to: http://127.0.0.1:8000/admin\n";
echo "3. Login with: {$email} / password\n\n";

