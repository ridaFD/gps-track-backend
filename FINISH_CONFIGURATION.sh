#!/bin/bash

# 🚀 Quick Configuration Script for Advanced Features
# Run this to complete the setup of all installed packages

echo "════════════════════════════════════════════════════════════════"
echo "🚀 GPS Track - Advanced Features Configuration"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Laravel project root"
    echo "   cd /Users/ridafakherlden/www/gps-track-backend"
    exit 1
fi

echo "✅ Detected Laravel project"
echo ""

# Step 1: Enable Scout Search
echo "📍 Step 1: Configuring Scout Search..."
if grep -q "SCOUT_DRIVER" .env; then
    echo "   ✅ SCOUT_DRIVER already in .env"
else
    echo "SCOUT_DRIVER=database" >> .env
    echo "   ✅ Added SCOUT_DRIVER=database to .env"
fi

# Step 2: Index existing data
echo ""
echo "📍 Step 2: Indexing existing devices for search..."
php artisan scout:import "App\Models\Device" 2>/dev/null && echo "   ✅ Devices indexed" || echo "   ⚠️  Run manually: php artisan scout:import 'App\Models\Device'"

# Step 3: Check Activity Log
echo ""
echo "📍 Step 3: Checking Activity Log..."
php artisan tinker --execute="echo 'Activity Log Status: ' . (class_exists('Spatie\Activitylog\Models\Activity') ? '✅ Working' : '❌ Not found');"

# Step 4: Test Sanctum
echo ""
echo "📍 Step 4: Checking Sanctum..."
php artisan tinker --execute="echo 'Sanctum Status: ' . (class_exists('Laravel\Sanctum\Sanctum') ? '✅ Installed' : '❌ Not found');"

# Step 5: Check Cashier
echo ""
echo "📍 Step 5: Checking Cashier..."
php artisan tinker --execute="echo 'Cashier Status: ' . (class_exists('Laravel\Cashier\Cashier') ? '✅ Installed' : '❌ Not found');"

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "✅ Automatic Configuration Complete!"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "⚠️  MANUAL STEPS REQUIRED:"
echo ""
echo "1. API Authentication (Sanctum):"
echo "   - Edit: routes/api.php"
echo "   - Replace mock login (line 35) with real Sanctum auth"
echo "   - Change: middleware('api') → middleware('auth:sanctum')"
echo ""
echo "2. WebSockets (Pusher):"
echo "   - Sign up: https://pusher.com"
echo "   - Get API keys"
echo "   - Update .env:"
echo "     PUSHER_APP_ID=your-app-id"
echo "     PUSHER_APP_KEY=your-app-key"
echo "     PUSHER_APP_SECRET=your-app-secret"
echo ""
echo "3. Billing (Cashier) - Optional:"
echo "   - Sign up: https://stripe.com"
echo "   - Get test API keys"
echo "   - Update .env:"
echo "     STRIPE_KEY=pk_test_..."
echo "     STRIPE_SECRET=sk_test_..."
echo ""
echo "════════════════════════════════════════════════════════════════"
echo "📖 Full Documentation:"
echo "   - ADVANCED_FEATURES_SETUP.md"
echo "   - ADVANCED_FEATURES_SUMMARY.md"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "🎉 Configuration complete! Next steps in documentation."
echo ""

