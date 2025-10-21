<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for GPS Tracking
        $permissions = [
            // Device permissions
            'view devices',
            'create devices',
            'edit devices',
            'delete devices',
            
            // Position permissions
            'view positions',
            'export positions',
            
            // Geofence permissions
            'view geofences',
            'create geofences',
            'edit geofences',
            'delete geofences',
            
            // Alert permissions
            'view alerts',
            'manage alert rules',
            'delete alerts',
            
            // Report permissions
            'view reports',
            'generate reports',
            'download reports',
            'delete reports',
            
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Role management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            
            // System
            'view activity log',
            'view system settings',
            'edit system settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin - has all permissions
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - can manage everything except system settings
        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo([
            'view devices', 'create devices', 'edit devices', 'delete devices',
            'view positions', 'export positions',
            'view geofences', 'create geofences', 'edit geofences', 'delete geofences',
            'view alerts', 'manage alert rules', 'delete alerts',
            'view reports', 'generate reports', 'download reports', 'delete reports',
            'view users', 'create users', 'edit users',
            'view activity log',
        ]);

        // Manager - can view and edit, but not delete
        $manager = Role::create(['name' => 'Manager']);
        $manager->givePermissionTo([
            'view devices', 'create devices', 'edit devices',
            'view positions', 'export positions',
            'view geofences', 'create geofences', 'edit geofences',
            'view alerts',
            'view reports', 'generate reports', 'download reports',
        ]);

        // Dispatcher - focused on monitoring and alerts
        $dispatcher = Role::create(['name' => 'Dispatcher']);
        $dispatcher->givePermissionTo([
            'view devices',
            'view positions',
            'view geofences',
            'view alerts',
            'view reports', 'download reports',
        ]);

        // Viewer - read-only access
        $viewer = Role::create(['name' => 'Viewer']);
        $viewer->givePermissionTo([
            'view devices',
            'view positions',
            'view geofences',
            'view alerts',
            'view reports',
        ]);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->table(
            ['Role', 'Permissions Count'],
            [
                ['Super Admin', $superAdmin->permissions->count()],
                ['Admin', $admin->permissions->count()],
                ['Manager', $manager->permissions->count()],
                ['Dispatcher', $dispatcher->permissions->count()],
                ['Viewer', $viewer->permissions->count()],
            ]
        );
    }
}

