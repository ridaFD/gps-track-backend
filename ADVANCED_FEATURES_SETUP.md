# 🚀 Advanced Features Setup Guide

## ✅ **Installed & Configured Features**

### **1. Spatie Permission / Orchid RBAC** ✅

**Status:** ✅ Using Orchid's Built-in RBAC System

**What's Available:**
- Orchid already includes a complete RBAC system
- `roles` and `permissions` tables exist
- Admin panel at `/admin` has user/role management

**How to Use:**

#### **A. Via Orchid Admin Panel:**
1. Login to `/admin`
2. Go to **System** → **Users**  
3. Go to **System** → **Roles**
4. Create roles and assign permissions

#### **B. Via Code:**
```php
// Get user's roles
$user->getRoles();

// Check permission
$user->hasAccess('platform.index');

// Assign role
$user->addRole($roleId);
```

**Orchid Permissions List:**
- `platform.index` - Access admin panel
- `platform.systems.users` - Manage users
- `platform.systems.roles` - Manage roles
- Custom: `platform.devices`, `platform.geofences`, `platform.alerts`, `platform.positions`

---

### **2. Laravel Sanctum (API Authentication)** ✅

**Status:** ✅ Installed, Ready to Configure

**Migrations:** ✅ Run (`personal_access_tokens` table created)

**Configuration:**

#### **Step 1: Add Sanctum Middleware to API Routes**

Already configured in `app/Http/Kernel.php`:
```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

#### **Step 2: Enable CORS**

Update `config/cors.php`:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'supports_credentials' => true,
```

#### **Step 3: API Authentication Endpoints**

**Login:**
```php
Route::post('/api/v1/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user,
    ]);
});
```

**Logout:**
```php
Route::post('/api/v1/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out']);
})->middleware('auth:sanctum');
```

**Get Current User:**
```php
Route::get('/api/v1/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
```

#### **Step 4: Protect API Routes**

In `routes/api.php`:
```php
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // All your protected routes here
    Route::get('/devices', [DeviceController::class, 'index']);
    Route::get('/positions', [PositionController::class, 'index']);
    // etc...
});
```

#### **Step 5: Frontend Usage**

**Login:**
```javascript
const response = await fetch('http://localhost:8000/api/v1/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        email: 'user@example.com',
        password: 'password'
    })
});

const { token, user } = await response.json();
localStorage.setItem('auth_token', token);
```

**Make Authenticated Requests:**
```javascript
const response = await fetch('http://localhost:8000/api/v1/devices', {
    headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
    }
});
```

---

### **3. Spatie Activity Log (Audit Trail)** ✅

**Status:** ✅ Installed & Configured

**Migrations:** ✅ Run (`activity_log` table created)

**Models Updated:** ✅ `User` model includes `LogsActivity` trait

**How to Use:**

#### **Auto-Logging (User Model):**
```php
// Already configured in User model
use Spatie\Activitylog\Traits\LogsActivity;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'email'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}
```

#### **Manual Logging:**
```php
// Log an activity
activity()
    ->causedBy($user)
    ->performedOn($device)
    ->withProperties(['old' => $oldData, 'new' => $newData])
    ->log('Device updated');

// Log with model
activity()
    ->on($device)
    ->log('created');
```

#### **Retrieve Activity:**
```php
// Get all activities
$activities = Activity::all();

// Get activities for a model
$activities = Activity::forSubject($device)->get();

// Get activities by causer
$activities = Activity::causedBy($user)->get();

// Get recent activities
$activities = Activity::latest()->take(10)->get();
```

#### **Display in Admin Panel:**

Create an Orchid screen to view activity log:
```php
namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Spatie\Activitylog\Models\Activity;

class ActivityLogScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'activities' => Activity::with(['causer', 'subject'])
                ->latest()
                ->paginate(50)
        ];
    }

    // ... layout implementation
}
```

---

### **4. Laravel Scout (Search)** ⚠️

**Status:** ✅ Installed, ⚠️ Needs Search Driver

**Options:**
- **Meilisearch** (Recommended for production)
- **Algolia** (Cloud service)
- **Database** (Simple, built-in)

#### **Option A: Database Driver (Quick Start)**

Update `.env`:
```env
SCOUT_DRIVER=database
```

Update models:
```php
use Laravel\Scout\Searchable;

class Device extends Model
{
    use Searchable;

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'imei' => $this->imei,
            'plate_number' => $this->plate_number,
        ];
    }
}
```

Search:
```php
Device::search('Vehicle')->get();
```

#### **Option B: Meilisearch (Production)**

1. Install Meilisearch:
```bash
# macOS
brew install meilisearch
brew services start meilisearch

# Or Docker
docker run -d -p 7700:7700 getmeili/meilisearch:latest
```

2. Install driver:
```bash
composer require meilisearch/meilisearch-php http-interop/http-factory-guzzle
```

3. Configure `.env`:
```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=
```

4. Index data:
```bash
php artisan scout:import "App\Models\Device"
```

---

### **5. Laravel Cashier (Billing)** ✅

**Status:** ✅ Installed & Configured

**Migrations:** ✅ Run (subscription tables created)

**Configuration:**

#### **Step 1: Get Stripe API Keys**

1. Sign up at [stripe.com](https://stripe.com)
2. Get test API keys from Dashboard
3. Update `.env`:
```env
STRIPE_KEY=pk_test_xxxxx
STRIPE_SECRET=sk_test_xxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxx
```

#### **Step 2: Update User Model**

```php
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use Billable;
}
```

#### **Step 3: Create Subscription Plans**

In Stripe Dashboard, create products/prices or use API:
```php
// Create a subscription
$user->newSubscription('default', 'price_xxxxx')->create($paymentMethod);

// Check subscription
if ($user->subscribed('default')) {
    // User has active subscription
}

// Cancel subscription
$user->subscription('default')->cancel();
```

#### **Step 4: Webhook Handler**

In `routes/web.php`:
```php
Route::post(
    'stripe/webhook',
    [WebhookController::class, 'handleWebhook']
);
```

#### **Step 5: Billing Portal**

```php
// Generate billing portal link
$url = $user->billingPortalUrl(route('dashboard'));
```

---

### **6. WebSocket Broadcasting** ⚠️

**Status:** ⚠️ Configured with Pusher, Needs Testing

**Current Setup:**
- Broadcasting driver: `pusher`
- Events created: `DevicePositionUpdated`, `AlertCreated`, `DeviceStatusChanged`
- Laravel Echo configured in frontend

**Configuration:**

#### **Option A: Use Pusher (Cloud Service)**

1. Sign up at [pusher.com](https://pusher.com)
2. Create a Channels app
3. Update `.env`:
```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=xxxxx
PUSHER_APP_KEY=xxxxx
PUSHER_APP_SECRET=xxxxx
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=us2
```

#### **Option B: Self-Hosted (Soketi)**

1. Install Soketi:
```bash
npm install -g @soketi/soketi
```

2. Run Soketi:
```bash
soketi start
```

3. Update `.env`:
```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=app-id
PUSHER_APP_KEY=app-key
PUSHER_APP_SECRET=app-secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

4. Update frontend Echo config:
```javascript
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'app-key',
    wsHost: '127.0.0.1',
    wsPort: 6001,
    forceTLS: false
});
```

#### **Test Broadcasting:**

```php
// Dispatch an event
broadcast(new DevicePositionUpdated($position));

// Listen in frontend
Echo.channel('devices')
    .listen('.position.updated', (e) => {
        console.log(e);
    });
```

---

## 📊 **Feature Status Summary**

| Feature | Status | Configuration Required |
|---------|--------|------------------------|
| **RBAC** | ✅ Complete | Use Orchid admin panel |
| **Sanctum** | ✅ Installed | Add API routes & middleware |
| **Activity Log** | ✅ Complete | Already logging User changes |
| **Scout** | ⚠️ Partial | Choose & configure search driver |
| **Cashier** | ✅ Installed | Add Stripe keys & subscription logic |
| **WebSockets** | ⚠️ Partial | Test with Pusher/Soketi |

---

## 🎯 **Quick Start Commands**

```bash
# Check installed packages
composer show | grep -E "(sanctum|permission|activity|scout|cashier)"

# Run all migrations
php artisan migrate

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Index models for search
php artisan scout:import "App\Models\Device"
php artisan scout:import "App\Models\Alert"

# Check Sanctum routes
php artisan route:list | grep sanctum

# Test Activity Log
php artisan tinker
>>> activity()->log('test');
>>> \Spatie\Activitylog\Models\Activity::latest()->first();
```

---

## 📖 **Next Steps**

### **Priority 1: Enable API Authentication**
1. Update `routes/api.php` with `auth:sanctum` middleware
2. Create login/logout endpoints
3. Test with Postman/frontend

### **Priority 2: Configure Search**
1. Choose search driver (Meilisearch recommended)
2. Add `Searchable` trait to models
3. Index existing data
4. Add search UI to frontend

### **Priority 3: Test WebSockets**
1. Sign up for Pusher OR install Soketi
2. Update `.env` with credentials
3. Test broadcasting events
4. Update frontend to listen for events

### **Priority 4: Add Billing (If Needed)**
1. Get Stripe API keys
2. Create subscription plans
3. Add subscription UI
4. Set up webhooks

---

## 🐛 **Troubleshooting**

### **Sanctum: 401 Unauthorized**
- Check `auth:sanctum` middleware is applied
- Verify token is sent in `Authorization: Bearer {token}` header
- Check CORS configuration allows credentials

### **Activity Log: Not Logging**
- Ensure model uses `LogsActivity` trait
- Check `getActivitylogOptions()` is configured
- Verify `activity_log` table exists

### **Scout: Search Not Working**
- Check `SCOUT_DRIVER` in `.env`
- Run `php artisan scout:import`  
- Verify search driver service is running (Meilisearch/Algolia)

### **Cashier: Stripe Errors**
- Verify API keys in `.env`
- Check Stripe dashboard for test mode
- Ensure webhook secret is correct

### **WebSockets: Not Connecting**
- Check Pusher/Soketi is running
- Verify credentials in `.env`
- Check browser console for connection errors

---

**Last Updated:** October 22, 2025  
**Version:** 2.0  
**Status:** ✅ 4/6 Features Fully Configured

