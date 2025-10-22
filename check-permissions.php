#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$email = $argv[1] ?? 'admin@admin.com';

echo "\n🔍 Checking permissions for: $email\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ User not found!\n\n";
    exit(1);
}

echo "✅ User found:\n";
echo "   Name: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   ID: {$user->id}\n\n";

echo "🔐 Current permissions:\n";
if (empty($user->permissions)) {
    echo "   ❌ NO PERMISSIONS SET!\n\n";
    echo "This is why you're getting 403!\n";
    echo "Run: php grant-admin.php {$email}\n\n";
} else {
    foreach ($user->permissions as $permission) {
        echo "   ✅ $permission\n";
    }
    echo "\n✅ User has " . count($user->permissions) . " permissions\n\n";
}

