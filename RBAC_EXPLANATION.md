# 🔐 RBAC System - Using Orchid (Not Spatie Permission)

## ⚠️ **Important: Conflict Resolved**

**Issue:** Spatie Permission conflicts with Orchid's built-in RBAC system.

**Error Message:**
```
Declaration of Spatie\Permission\Traits\HasRoles::removeRole(...$role) 
must be compatible with Orchid\Platform\Models\User::removeRole(Orchid\Access\RoleInterface $role)
```

**Root Cause:** Both packages define role management methods (`removeRole`, `addRole`, etc.) with different signatures.

**Solution:** ✅ **Use Orchid's built-in RBAC system** (already installed and working)

---

## ✅ **What We're Using: Orchid RBAC**

Orchid Platform includes a **complete, production-ready RBAC system** out of the box:

### **Features:**
- ✅ User management
- ✅ Role management  
- ✅ Permission management
- ✅ Built-in UI at `/admin`
- ✅ Already configured and working
- ✅ Database tables already exist (`roles`, `permissions`, `role_users`)

### **Why Use Orchid RBAC:**
1. **Already Installed** - Came with Orchid Platform
2. **UI Included** - Beautiful admin interface
3. **Well Integrated** - Works seamlessly with Orchid screens
4. **No Conflicts** - Designed to work together
5. **Production Ready** - Used by thousands of Laravel apps

---

## 🎯 **How to Use Orchid RBAC**

### **1. Via Admin Panel** (Easiest)

**Access:** http://localhost:8000/admin

**Steps:**
1. Login to admin panel
2. Go to **System** → **Users**
3. Go to **System** → **Roles**
4. Create/edit roles
5. Assign permissions
6. Assign roles to users

---

### **2. Via Code**

#### **Check Permissions:**
```php
// Check if user has access to a permission
if ($user->hasAccess('platform.index')) {
    // User can access admin panel
}

// Check multiple permissions
if ($user->hasAccess(['platform.devices', 'platform.geofences'])) {
    // User has both permissions
}
```

#### **Get User Roles:**
```php
// Get all roles for a user
$roles = $user->getRoles();

// Check if user has a specific role
foreach ($user->roles as $role) {
    if ($role->slug === 'admin') {
        // User is an admin
    }
}
```

#### **Assign Roles:**
```php
// Add role to user
$user->addRole($roleId);

// Remove role from user  
$user->removeRole($roleId);

// Replace all roles
$user->replaceRoles([$roleId1, $roleId2]);
```

#### **Check in Controllers:**
```php
use Illuminate\Support\Facades\Auth;

public function index()
{
    if (!Auth::user()->hasAccess('platform.devices')) {
        abort(403, 'Unauthorized');
    }
    
    // ... your code
}
```

#### **Check in Blade Views:**
```php
@can('access', 'platform.devices')
    <button>Manage Devices</button>
@endcan
```

#### **Check in Routes:**
```php
Route::middleware(['auth', 'access:platform.devices'])
    ->group(function () {
        Route::get('/devices', [DeviceController::class, 'index']);
    });
```

---

## 📋 **Available Permissions**

### **System Permissions (Built-in):**
- `platform.index` - Access admin panel
- `platform.systems.users` - Manage users
- `platform.systems.roles` - Manage roles
- `platform.systems.attachment` - Manage attachments

### **Custom Permissions (Our GPS App):**
- `platform.devices` - Manage devices
- `platform.geofences` - Manage geofences
- `platform.alerts` - Manage alerts
- `platform.positions` - View GPS positions

---

## 🛠️ **Create Custom Permissions**

### **Via Migration:**

Create a new seeder:
```bash
php artisan make:seeder PermissionSeeder
```

In `database/seeders/PermissionSeeder.php`:
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Orchid\Platform\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Get or create role
        $admin = Role::firstOrCreate([
            'slug' => 'admin',
            'name' => 'Administrator',
        ]);

        // Set permissions
        $admin->permissions = [
            'platform.index' => true,
            'platform.systems.users' => true,
            'platform.devices' => true,
            'platform.geofences' => true,
            'platform.alerts' => true,
            'platform.positions' => true,
        ];
        
        $admin->save();
    }
}
```

Run seeder:
```bash
php artisan db:seed --class=PermissionSeeder
```

---

### **Via Orchid Platform Provider:**

In `app/Orchid/PlatformProvider.php`, the `permissions()` method already defines custom permissions:

```php
public function permissions(): array
{
    return [
        ItemPermission::group('GPS Tracking')
            ->addPermission('platform.devices', 'Manage Devices')
            ->addPermission('platform.geofences', 'Manage Geofences')
            ->addPermission('platform.alerts', 'Manage Alerts')
            ->addPermission('platform.positions', 'View GPS Positions'),

        ItemPermission::group(__('System'))
            ->addPermission('platform.systems.roles', __('Roles'))
            ->addPermission('platform.systems.users', __('Users')),
    ];
}
```

These permissions automatically appear in the admin panel!

---

## 🔒 **Protect Orchid Screens**

Add permissions to your custom screens:

```php
namespace App\Orchid\Screens;

use Orchid\Screen\Screen;

class DeviceListScreen extends Screen
{
    /**
     * Permission required to view this screen
     */
    public function permission(): ?iterable
    {
        return [
            'platform.devices',
        ];
    }

    // ... rest of screen
}
```

---

## 🆚 **Orchid RBAC vs Spatie Permission**

| Feature | Orchid RBAC | Spatie Permission |
|---------|-------------|-------------------|
| **Admin UI** | ✅ Built-in | ❌ No UI |
| **Integration** | ✅ Native | ⚠️ Requires setup |
| **Conflicts** | ✅ None | ❌ Conflicts with Orchid |
| **Permissions Storage** | JSON in roles table | Separate permissions table |
| **Ease of Use** | ✅ Simple | ⚠️ More complex |
| **Production Ready** | ✅ Yes | ✅ Yes |

**Verdict:** ✅ **Use Orchid RBAC** - It's already installed, has a UI, and works perfectly with our system.

---

## 🎯 **Common Use Cases**

### **Use Case 1: Create Admin Role**

```php
use Orchid\Platform\Models\Role;

$admin = Role::create([
    'name' => 'Administrator',
    'slug' => 'admin',
    'permissions' => [
        'platform.index' => true,
        'platform.systems.users' => true,
        'platform.systems.roles' => true,
        'platform.devices' => true,
        'platform.geofences' => true,
        'platform.alerts' => true,
        'platform.positions' => true,
    ],
]);
```

### **Use Case 2: Create Viewer Role (Read-Only)**

```php
$viewer = Role::create([
    'name' => 'Viewer',
    'slug' => 'viewer',
    'permissions' => [
        'platform.index' => true,
        'platform.devices' => false,     // Can't manage
        'platform.geofences' => false,   // Can't manage
        'platform.alerts' => false,      // Can't manage
        'platform.positions' => true,    // Can view
    ],
]);
```

### **Use Case 3: Assign Role to User**

```php
$user = User::find(1);
$adminRole = Role::where('slug', 'admin')->first();

$user->addRole($adminRole->id);
```

### **Use Case 4: Check Permission in Orchid Screen**

```php
class DeviceListScreen extends Screen
{
    public function permission(): ?iterable
    {
        return ['platform.devices'];
    }

    public function commandBar(): iterable
    {
        return [
            // Only show "Create" button if user can edit
            Link::make('Create Device')
                ->icon('plus')
                ->route('platform.devices.create')
                ->canSee(auth()->user()->hasAccess('platform.devices')),
        ];
    }
}
```

---

## ✅ **Summary**

- ✅ **Spatie Permission removed** - Caused conflict
- ✅ **Orchid RBAC active** - Already working
- ✅ **User model fixed** - No more errors
- ✅ **Admin panel ready** - http://localhost:8000/admin
- ✅ **Permissions defined** - In `PlatformProvider.php`

**You can now:**
1. Create roles in admin panel
2. Assign permissions to roles
3. Assign roles to users
4. Check permissions in code with `$user->hasAccess('permission.name')`

---

## 📖 **Official Documentation**

- **Orchid RBAC:** https://orchid.software/en/docs/access
- **Orchid Screens:** https://orchid.software/en/docs/screens
- **Orchid Permissions:** https://orchid.software/en/docs/permissions

---

**Last Updated:** October 22, 2025  
**Status:** ✅ Working (Conflict Resolved)  
**RBAC System:** Orchid Platform (Built-in)

