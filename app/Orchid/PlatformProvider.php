<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Dashboard')
                ->icon('bs.speedometer2')
                ->title('🗺️ GPS Tracking')
                ->route('platform.main')
                ->badge(fn () => \App\Models\Alert::where('read', false)->count(), Color::DANGER),

            Menu::make('Devices')
                ->icon('bs.truck')
                ->route('platform.devices.list')
                ->badge(fn () => \App\Models\Device::where('status', 'active')->count(), Color::SUCCESS),

            Menu::make('Geofences')
                ->icon('bs.geo-alt')
                ->route('platform.geofences.list')
                ->badge(fn () => \App\Models\Geofence::where('active', true)->count(), Color::INFO),

            Menu::make('Alerts')
                ->icon('bs.bell')
                ->route('platform.alerts.list')
                ->badge(fn () => \App\Models\Alert::where('read', false)->count(), Color::WARNING),

            Menu::make('GPS Positions')
                ->icon('bs.pin-map')
                ->route('platform.positions.list')
                ->divider(),

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('System Management')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles'),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
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
}
