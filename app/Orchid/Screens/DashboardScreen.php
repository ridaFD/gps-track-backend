<?php

namespace App\Orchid\Screens;

use App\Models\Device;
use App\Models\Position;
use App\Models\Geofence;
use App\Models\Alert;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Sight;

class DashboardScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $totalDevices = Device::count();
        $activeDevices = Device::where('status', 'active')->count();
        $inactiveDevices = Device::where('status', 'inactive')->count();
        $maintenanceDevices = Device::where('status', 'maintenance')->count();
        
        $totalGeofences = Geofence::where('active', true)->count();
        $totalAlerts = Alert::count();
        $unreadAlerts = Alert::where('read', false)->count();
        $todayAlerts = Alert::whereDate('created_at', today())->count();
        
        $totalPositions = Position::count();
        $todayPositions = Position::whereDate('created_at', today())->count();
        
        // Recent alerts
        $recentAlerts = Alert::with(['device', 'geofence'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Active devices with last position
        $activeDevicesData = Device::where('status', 'active')
            ->with(['lastPosition'])
            ->limit(10)
            ->get();

        return [
            'metrics' => [
                'total_devices' => $totalDevices,
                'active_devices' => $activeDevices,
                'inactive_devices' => $inactiveDevices,
                'maintenance_devices' => $maintenanceDevices,
                'total_geofences' => $totalGeofences,
                'total_alerts' => $totalAlerts,
                'unread_alerts' => $unreadAlerts,
                'today_alerts' => $todayAlerts,
                'total_positions' => $totalPositions,
                'today_positions' => $todayPositions,
            ],
            'recent_alerts' => $recentAlerts,
            'active_devices' => $activeDevicesData,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return '🗺️ GPS Tracking Dashboard';
    }

    /**
     * The description of the screen.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Real-time overview of your GPS tracking system';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Total Devices' => 'metrics.total_devices',
                'Active Devices' => 'metrics.active_devices',
                'Inactive Devices' => 'metrics.inactive_devices',
                'Maintenance' => 'metrics.maintenance_devices',
            ]),
            
            Layout::metrics([
                'Active Geofences' => 'metrics.total_geofences',
                'Total Alerts' => 'metrics.total_alerts',
                'Unread Alerts' => 'metrics.unread_alerts',
                'Today\'s Alerts' => 'metrics.today_alerts',
            ]),
            
            Layout::metrics([
                'Total GPS Records' => 'metrics.total_positions',
                'Today\'s GPS Records' => 'metrics.today_positions',
            ]),

            Layout::columns([
                Layout::view('orchid.dashboard.recent-alerts'),
                Layout::view('orchid.dashboard.active-devices'),
            ]),
        ];
    }
}
