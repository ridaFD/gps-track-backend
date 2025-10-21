<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\Position;
use App\Models\Geofence;
use App\Models\Alert;
use Carbon\Carbon;

class GpsTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Devices
        $device1 = Device::create([
            'name' => 'Vehicle 001',
            'imei' => '123456789012345',
            'type' => 'car',
            'status' => 'active',
            'plate_number' => 'ABC-001',
            'model' => 'Toyota Camry',
            'color' => 'Silver',
            'year' => 2022,
            'driver_name' => 'John Smith',
            'driver_phone' => '+1234567890',
        ]);

        $device2 = Device::create([
            'name' => 'Truck 042',
            'imei' => '234567890123456',
            'type' => 'truck',
            'status' => 'active',
            'plate_number' => 'TRK-042',
            'model' => 'Ford F-150',
            'color' => 'Blue',
            'year' => 2021,
            'driver_name' => 'Jane Doe',
            'driver_phone' => '+1234567891',
        ]);

        $device3 = Device::create([
            'name' => 'Van 015',
            'imei' => '345678901234567',
            'type' => 'van',
            'status' => 'inactive',
            'plate_number' => 'VAN-015',
            'model' => 'Mercedes Sprinter',
            'color' => 'White',
            'year' => 2020,
            'driver_name' => 'Mike Johnson',
            'driver_phone' => '+1234567892',
        ]);

        // Create Positions
        Position::create([
            'device_id' => $device1->id,
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'altitude' => 10,
            'speed' => 45,
            'heading' => 90,
            'satellites' => 12,
            'accuracy' => 5,
            'fuel_level' => 78,
            'battery_level' => 95,
            'ignition' => true,
            'device_time' => Carbon::now()->subMinutes(2),
        ]);

        Position::create([
            'device_id' => $device2->id,
            'latitude' => 40.7589,
            'longitude' => -73.9851,
            'altitude' => 15,
            'speed' => 30,
            'heading' => 180,
            'satellites' => 10,
            'accuracy' => 8,
            'fuel_level' => 65,
            'battery_level' => 88,
            'ignition' => true,
            'device_time' => Carbon::now()->subMinutes(1),
        ]);

        Position::create([
            'device_id' => $device3->id,
            'latitude' => 40.7614,
            'longitude' => -73.9776,
            'altitude' => 12,
            'speed' => 0,
            'heading' => 0,
            'satellites' => 8,
            'accuracy' => 10,
            'fuel_level' => 45,
            'battery_level' => 60,
            'ignition' => false,
            'device_time' => Carbon::now()->subHours(2),
        ]);

        // Add more historical positions for device 1
        for ($i = 1; $i <= 10; $i++) {
            Position::create([
                'device_id' => $device1->id,
                'latitude' => 40.7128 + ($i * 0.001),
                'longitude' => -74.0060 + ($i * 0.001),
                'speed' => rand(30, 60),
                'heading' => rand(0, 360),
                'satellites' => rand(8, 15),
                'accuracy' => rand(3, 10),
                'device_time' => Carbon::now()->subMinutes($i * 5),
            ]);
        }

        // Create Geofences
        Geofence::create([
            'name' => 'Office Zone',
            'description' => 'Main office area',
            'type' => 'circle',
            'center_lat' => 40.7128,
            'center_lng' => -74.0060,
            'radius' => 500,
            'color' => '#3B82F6',
            'active' => true,
            'alert_on_enter' => true,
            'alert_on_exit' => true,
        ]);

        Geofence::create([
            'name' => 'Warehouse Area',
            'description' => 'Storage warehouse zone',
            'type' => 'polygon',
            'coordinates' => [
                ['lat' => 40.7589, 'lng' => -73.9851],
                ['lat' => 40.7590, 'lng' => -73.9840],
                ['lat' => 40.7580, 'lng' => -73.9840],
                ['lat' => 40.7580, 'lng' => -73.9851],
            ],
            'color' => '#10B981',
            'active' => true,
            'alert_on_enter' => false,
            'alert_on_exit' => true,
        ]);

        Geofence::create([
            'name' => 'Restricted Zone',
            'description' => 'No entry area',
            'type' => 'circle',
            'center_lat' => 40.7489,
            'center_lng' => -73.9680,
            'radius' => 300,
            'color' => '#EF4444',
            'active' => true,
            'alert_on_enter' => true,
            'alert_on_exit' => false,
        ]);

        // Create Alerts
        Alert::create([
            'device_id' => $device1->id,
            'type' => 'geofence_exit',
            'severity' => 'warning',
            'message' => 'Vehicle exited Office Zone',
            'data' => ['geofence' => 'Office Zone', 'time' => Carbon::now()->subMinutes(15)->toISOString()],
            'read' => false,
        ]);

        Alert::create([
            'device_id' => $device2->id,
            'type' => 'speed_limit',
            'severity' => 'high',
            'message' => 'Speed limit exceeded (85 km/h)',
            'data' => ['speed' => 85, 'limit' => 60],
            'read' => false,
        ]);

        Alert::create([
            'device_id' => $device3->id,
            'type' => 'idle',
            'severity' => 'info',
            'message' => 'Vehicle idle for 2 hours',
            'data' => ['duration' => 120],
            'read' => true,
            'read_at' => Carbon::now()->subHour(),
        ]);

        Alert::create([
            'device_id' => $device1->id,
            'type' => 'low_battery',
            'severity' => 'warning',
            'message' => 'Low battery level detected (25%)',
            'data' => ['battery' => 25],
            'read' => false,
        ]);

        Alert::create([
            'device_id' => $device2->id,
            'type' => 'geofence_entry',
            'severity' => 'info',
            'message' => 'Vehicle entered Warehouse Area',
            'data' => ['geofence' => 'Warehouse Area'],
            'read' => true,
            'read_at' => Carbon::now()->subMinutes(45),
        ]);
    }
}
