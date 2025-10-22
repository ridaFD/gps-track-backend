<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use App\Models\Device;
use App\Models\Geofence;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );

        $john = User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
            ]
        );

        $jane = User::firstOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Smith',
                'password' => Hash::make('password'),
            ]
        );

        // Create Organization 1 - Acme Corp (Professional Plan)
        $acmeCorp = Organization::create([
            'name' => 'Acme Corporation',
            'slug' => 'acme-corp',
            'email' => 'contact@acmecorp.com',
            'phone' => '+1-555-0100',
            'address' => '123 Business St, New York, NY 10001',
            'plan' => 'professional',
            'max_devices' => 50,
            'max_users' => 20,
            'is_active' => true,
            'trial_ends_at' => now()->addDays(14),
        ]);

        // Add users to Acme Corp
        $acmeCorp->users()->attach($admin->id, ['role' => 'owner']);
        $acmeCorp->users()->attach($john->id, ['role' => 'admin']);

        // Create devices for Acme Corp
        $acmeDevices = [
            [
                'organization_id' => $acmeCorp->id,
                'name' => 'Fleet Vehicle 001',
                'imei' => '356938035643809',
                'type' => 'car',
                'status' => 'active',
                'plate_number' => 'ACM-101',
                'model' => 'Toyota Camry 2023',
                'color' => 'Blue',
                'driver_name' => 'Tom Wilson',
                'driver_phone' => '+1-555-0201',
                'user_id' => $admin->id,
            ],
            [
                'organization_id' => $acmeCorp->id,
                'name' => 'Fleet Vehicle 002',
                'imei' => '356938035643810',
                'type' => 'truck',
                'status' => 'active',
                'plate_number' => 'ACM-102',
                'model' => 'Ford F-150 2023',
                'color' => 'Red',
                'driver_name' => 'Sarah Johnson',
                'driver_phone' => '+1-555-0202',
                'user_id' => $admin->id,
            ],
            [
                'organization_id' => $acmeCorp->id,
                'name' => 'Delivery Van 001',
                'imei' => '356938035643811',
                'type' => 'van',
                'status' => 'active',
                'plate_number' => 'ACM-103',
                'model' => 'Mercedes Sprinter 2023',
                'color' => 'White',
                'driver_name' => 'Mike Brown',
                'driver_phone' => '+1-555-0203',
                'user_id' => $john->id,
            ],
        ];

        foreach ($acmeDevices as $deviceData) {
            Device::create($deviceData);
        }

        // Create geofences for Acme Corp
        Geofence::create([
            'organization_id' => $acmeCorp->id,
            'name' => 'Acme Headquarters',
            'description' => 'Main office building',
            'type' => 'circle',
            'center_lat' => 40.7128,
            'center_lng' => -74.0060,
            'radius' => 500,
            'color' => '#007bff',
            'active' => true,
            'alert_on_enter' => false,
            'alert_on_exit' => true,
            'user_id' => $admin->id,
        ]);

        Geofence::create([
            'organization_id' => $acmeCorp->id,
            'name' => 'Warehouse District',
            'description' => 'Delivery zone',
            'type' => 'circle',
            'center_lat' => 40.7589,
            'center_lng' => -73.9851,
            'radius' => 1000,
            'color' => '#28a745',
            'active' => true,
            'alert_on_enter' => true,
            'alert_on_exit' => true,
            'user_id' => $admin->id,
        ]);

        // Create Organization 2 - TechStart Inc (Starter Plan)
        $techstart = Organization::create([
            'name' => 'TechStart Inc',
            'slug' => 'techstart-inc',
            'email' => 'info@techstart.com',
            'phone' => '+1-555-0300',
            'address' => '456 Startup Ave, San Francisco, CA 94102',
            'plan' => 'starter',
            'max_devices' => 10,
            'max_users' => 5,
            'is_active' => true,
            'trial_ends_at' => now()->addDays(7),
        ]);

        // Add users to TechStart
        $techstart->users()->attach($jane->id, ['role' => 'owner']);

        // Create devices for TechStart
        $techstartDevices = [
            [
                'organization_id' => $techstart->id,
                'name' => 'Company Car',
                'imei' => '356938035643812',
                'type' => 'car',
                'status' => 'active',
                'plate_number' => 'TSI-001',
                'model' => 'Tesla Model 3 2023',
                'color' => 'Black',
                'driver_name' => 'Jane Smith',
                'driver_phone' => '+1-555-0301',
                'user_id' => $jane->id,
            ],
            [
                'organization_id' => $techstart->id,
                'name' => 'Bike Courier',
                'imei' => '356938035643813',
                'type' => 'motorcycle',
                'status' => 'active',
                'plate_number' => 'TSI-002',
                'model' => 'Honda CBR 2023',
                'color' => 'Green',
                'driver_name' => 'Alex Chen',
                'driver_phone' => '+1-555-0302',
                'user_id' => $jane->id,
            ],
        ];

        foreach ($techstartDevices as $deviceData) {
            Device::create($deviceData);
        }

        // Create geofence for TechStart
        Geofence::create([
            'organization_id' => $techstart->id,
            'name' => 'TechStart Office',
            'description' => 'Main office in SF',
            'type' => 'circle',
            'center_lat' => 37.7749,
            'center_lng' => -122.4194,
            'radius' => 300,
            'color' => '#6f42c1',
            'active' => true,
            'alert_on_enter' => false,
            'alert_on_exit' => true,
            'user_id' => $jane->id,
        ]);

        // Create Organization 3 - Global Logistics (Enterprise Plan)
        $globalLogistics = Organization::create([
            'name' => 'Global Logistics Ltd',
            'slug' => 'global-logistics',
            'email' => 'operations@globallogistics.com',
            'phone' => '+1-555-0400',
            'address' => '789 Commerce Blvd, Chicago, IL 60601',
            'plan' => 'enterprise',
            'max_devices' => 999999,
            'max_users' => 999999,
            'is_active' => true,
            'subscription_ends_at' => now()->addYear(),
        ]);

        // Add admin to Global Logistics
        $globalLogistics->users()->attach($admin->id, ['role' => 'member']);

        // Create sample device for Global Logistics
        Device::create([
            'organization_id' => $globalLogistics->id,
            'name' => 'Freight Truck Alpha',
            'imei' => '356938035643814',
            'type' => 'truck',
            'status' => 'active',
            'plate_number' => 'GLL-001',
            'model' => 'Volvo VNL 860 2023',
            'color' => 'Silver',
            'driver_name' => 'Robert Davis',
            'driver_phone' => '+1-555-0401',
            'user_id' => $admin->id,
        ]);

        $this->command->info('✅ Organizations seeded successfully!');
        $this->command->info('');
        $this->command->info('📊 Created:');
        $this->command->info('  - 3 Organizations (Acme Corp, TechStart, Global Logistics)');
        $this->command->info('  - 3 Users (admin@admin.com, john@example.com, jane@example.com)');
        $this->command->info('  - 6 Devices across organizations');
        $this->command->info('  - 3 Geofences');
        $this->command->info('');
        $this->command->info('🔑 Login credentials:');
        $this->command->info('  - admin@admin.com / password (Acme Corp + Global Logistics)');
        $this->command->info('  - john@example.com / password (Acme Corp)');
        $this->command->info('  - jane@example.com / password (TechStart)');
    }
}
