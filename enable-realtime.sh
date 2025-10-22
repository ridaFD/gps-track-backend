#!/bin/bash

# ============================================================================
# GPS Track - Enable Real-Time Maps with Pusher
# ============================================================================
# This script helps you set up Pusher for real-time GPS tracking
# Time: ~15 minutes
# ============================================================================

echo ""
echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║                                                               ║"
echo "║         🔴 ENABLE REAL-TIME MAPS WITH PUSHER                  ║"
echo "║                                                               ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ ERROR: .env file not found!"
    echo "   Please copy .env.example to .env first"
    exit 1
fi

echo "📋 Step 1: Get Pusher Credentials"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "1. Open your browser and go to: https://pusher.com/"
echo "2. Sign up or log in"
echo "3. Create a new app (or use existing one)"
echo "4. Choose 'Channels' product"
echo "5. Copy your credentials:"
echo "   - App ID"
echo "   - Key"
echo "   - Secret"
echo "   - Cluster (e.g., us2, eu, ap1)"
echo ""
read -p "Press ENTER when you have your credentials ready..."
echo ""

echo "🔧 Step 2: Enter Your Pusher Credentials"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

read -p "Enter your Pusher App ID: " PUSHER_APP_ID
read -p "Enter your Pusher App Key: " PUSHER_APP_KEY
read -p "Enter your Pusher App Secret: " PUSHER_APP_SECRET
read -p "Enter your Pusher App Cluster (e.g., us2, eu, ap1): " PUSHER_APP_CLUSTER

# Validate inputs
if [ -z "$PUSHER_APP_ID" ] || [ -z "$PUSHER_APP_KEY" ] || [ -z "$PUSHER_APP_SECRET" ] || [ -z "$PUSHER_APP_CLUSTER" ]; then
    echo ""
    echo "❌ ERROR: All fields are required!"
    exit 1
fi

echo ""
echo "📝 Step 3: Updating .env file..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Update BROADCAST_DRIVER
if grep -q "BROADCAST_DRIVER=" .env; then
    sed -i.bak "s/BROADCAST_DRIVER=.*/BROADCAST_DRIVER=pusher/" .env
else
    echo "BROADCAST_DRIVER=pusher" >> .env
fi

# Update Pusher credentials
sed -i.bak "s/PUSHER_APP_ID=.*/PUSHER_APP_ID=$PUSHER_APP_ID/" .env
sed -i.bak "s/PUSHER_APP_KEY=.*/PUSHER_APP_KEY=$PUSHER_APP_KEY/" .env
sed -i.bak "s/PUSHER_APP_SECRET=.*/PUSHER_APP_SECRET=$PUSHER_APP_SECRET/" .env
sed -i.bak "s/PUSHER_APP_CLUSTER=.*/PUSHER_APP_CLUSTER=$PUSHER_APP_CLUSTER/" .env

# Set default values for cloud Pusher
sed -i.bak "s/PUSHER_HOST=.*/PUSHER_HOST=/" .env
sed -i.bak "s/PUSHER_PORT=.*/PUSHER_PORT=443/" .env
sed -i.bak "s/PUSHER_SCHEME=.*/PUSHER_SCHEME=https/" .env

# Remove backup file
rm -f .env.bak

echo "✅ Backend .env updated!"
echo ""

echo "🧹 Step 4: Clearing Laravel cache..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php artisan config:clear
php artisan cache:clear
echo "✅ Cache cleared!"
echo ""

echo "🧪 Step 5: Testing Pusher connection..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
php test-pusher.php
echo ""

echo "🎨 Step 6: Update Frontend .env"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Create a .env file in your frontend directory:"
echo "/Users/ridafakherlden/www/gps-track/.env"
echo ""
echo "Add these lines:"
echo ""
echo "REACT_APP_API_URL=http://localhost:8000/api/v1"
echo "REACT_APP_PUSHER_KEY=$PUSHER_APP_KEY"
echo "REACT_APP_PUSHER_CLUSTER=$PUSHER_APP_CLUSTER"
echo "REACT_APP_PUSHER_HOST="
echo "REACT_APP_PUSHER_PORT=443"
echo "REACT_APP_PUSHER_SCHEME=https"
echo ""
read -p "Press ENTER after you've created the frontend .env file..."
echo ""

echo "✅ SETUP COMPLETE!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🚀 Next Steps:"
echo ""
echo "1. Start your backend (if not running):"
echo "   php artisan serve"
echo ""
echo "2. Restart your frontend:"
echo "   cd /Users/ridafakherlden/www/gps-track"
echo "   npm start"
echo ""
echo "3. Open Pusher Dashboard:"
echo "   https://dashboard.pusher.com/"
echo "   Go to 'Debug Console' tab"
echo ""
echo "4. Open your app:"
echo "   http://localhost:3000"
echo "   Check browser console - you should see 'Pusher connected'"
echo ""
echo "5. Test real-time updates:"
echo "   Run: php test-pusher.php"
echo "   Check Pusher Debug Console for events!"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📖 For more details, see: PUSHER_SETUP_GUIDE.md"
echo ""
echo "🎉 Real-time tracking is now ENABLED!"
echo ""

