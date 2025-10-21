#!/bin/bash

echo "🚀 Starting GPS Track Backend Setup..."

# Check if Laravel is installed
if [ ! -f "composer.json" ]; then
    echo "📦 Installing Laravel..."
    composer create-project laravel/laravel . --prefer-dist
fi

# Start Docker services
echo "🐳 Starting Docker services..."
docker-compose up -d

# Wait for services to be ready
echo "⏳ Waiting for services to start..."
sleep 10

# Install dependencies
echo "📦 Installing Composer dependencies..."
docker-compose exec -T laravel composer install

# Copy environment file
if [ ! -f ".env" ]; then
    echo "⚙️  Creating environment file..."
    cp .env.example .env 2>/dev/null || docker-compose exec -T laravel cp .env.example .env
fi

# Generate app key
echo "🔑 Generating application key..."
docker-compose exec -T laravel php artisan key:generate

# Run migrations
echo "📊 Running database migrations..."
docker-compose exec -T laravel php artisan migrate --force

# Install Orchid
echo "🌸 Installing Orchid Platform..."
docker-compose exec -T laravel composer require orchid/platform
docker-compose exec -T laravel php artisan orchid:install

# Create admin user
echo "👤 Creating admin user..."
docker-compose exec -T laravel php artisan orchid:admin admin admin@example.com password

echo "✅ Backend setup complete!"
echo ""
echo "🌐 Access points:"
echo "   - API: http://localhost:8000"
echo "   - Admin Panel: http://localhost:8000/admin"
echo "   - Credentials: admin@example.com / password"
echo ""
echo "To view logs: docker-compose logs -f"
echo "To stop: docker-compose down"
