<?php

declare(strict_types=1);

// GPS Tracking Screens
use App\Orchid\Screens\DashboardScreen;
use App\Orchid\Screens\DeviceListScreen;
use App\Orchid\Screens\DeviceEditScreen;
use App\Orchid\Screens\GeofenceListScreen;
use App\Orchid\Screens\GeofenceEditScreen;
use App\Orchid\Screens\AlertListScreen;
use App\Orchid\Screens\PositionListScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main Dashboard
Route::screen('/main', DashboardScreen::class)
    ->name('platform.main')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->push('🗺️ GPS Dashboard', route('platform.main')));

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn (Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

// GPS Tracking > Devices
Route::screen('devices', DeviceListScreen::class)
    ->name('platform.devices.list')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('🚗 Devices', route('platform.devices.list')));

Route::screen('devices/{device}/edit', DeviceEditScreen::class)
    ->name('platform.devices.edit')
    ->breadcrumbs(fn (Trail $trail, $device) => $trail
        ->parent('platform.devices.list')
        ->push('Edit Device', route('platform.devices.edit', $device)));

Route::screen('devices/create', DeviceEditScreen::class)
    ->name('platform.devices.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.devices.list')
        ->push('Create Device', route('platform.devices.create')));

// GPS Tracking > Geofences
Route::screen('geofences', GeofenceListScreen::class)
    ->name('platform.geofences.list')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('🗺️ Geofences', route('platform.geofences.list')));

Route::screen('geofences/{geofence}/edit', GeofenceEditScreen::class)
    ->name('platform.geofences.edit')
    ->breadcrumbs(fn (Trail $trail, $geofence) => $trail
        ->parent('platform.geofences.list')
        ->push('Edit Geofence', route('platform.geofences.edit', $geofence)));

Route::screen('geofences/create', GeofenceEditScreen::class)
    ->name('platform.geofences.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.geofences.list')
        ->push('Create Geofence', route('platform.geofences.create')));

// GPS Tracking > Alerts
Route::screen('alerts', AlertListScreen::class)
    ->name('platform.alerts.list')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('🚨 Alerts', route('platform.alerts.list')));

// GPS Tracking > Positions
Route::screen('positions', PositionListScreen::class)
    ->name('platform.positions.list')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('📍 GPS Positions', route('platform.positions.list')));
