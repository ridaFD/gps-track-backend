<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Health check endpoint
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'message' => 'GPS Track API is running',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
        ]);
    });

    // Mock authentication endpoints (for testing)
    Route::post('/login', function (Request $request) {
        return response()->json([
            'token' => 'mock-token-' . uniqid(),
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
        ]);
    });

    Route::post('/register', function (Request $request) {
        return response()->json([
            'message' => 'User registered successfully',
            'user' => [
                'id' => 1,
                'name' => $request->input('name', 'New User'),
                'email' => $request->input('email', 'user@example.com'),
            ],
        ]);
    });

    // Protected routes (mock - no actual auth for now)
    Route::middleware('api')->group(function () {
        
        // User info
        Route::get('/user', function (Request $request) {
            return response()->json([
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'company' => 'GPS Tracking Inc.',
                'role' => 'admin',
            ]);
        });

        // Dashboard statistics
        Route::get('/statistics/dashboard', function () {
            return response()->json([
                'total_assets' => 45,
                'active_assets' => 38,
                'geofences' => 12,
                'alerts_today' => 5,
                'distance_today' => 1250.5,
                'active_trips' => 8,
            ]);
        });

        // Devices/Assets
        Route::get('/devices', function () {
            return response()->json([
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Vehicle 001',
                        'type' => 'car',
                        'status' => 'active',
                        'last_position' => [
                            'lat' => 40.7128,
                            'lng' => -74.0060,
                            'speed' => 45,
                            'timestamp' => now()->subMinutes(2)->toISOString(),
                        ],
                    ],
                    [
                        'id' => 2,
                        'name' => 'Truck 042',
                        'type' => 'truck',
                        'status' => 'active',
                        'last_position' => [
                            'lat' => 40.7589,
                            'lng' => -73.9851,
                            'speed' => 30,
                            'timestamp' => now()->subMinutes(1)->toISOString(),
                        ],
                    ],
                    [
                        'id' => 3,
                        'name' => 'Van 015',
                        'type' => 'van',
                        'status' => 'inactive',
                        'last_position' => [
                            'lat' => 40.7614,
                            'lng' => -73.9776,
                            'speed' => 0,
                            'timestamp' => now()->subHours(2)->toISOString(),
                        ],
                    ],
                ],
                'meta' => [
                    'total' => 3,
                    'per_page' => 15,
                    'current_page' => 1,
                ],
            ]);
        });

        Route::post('/devices', function (Request $request) {
            return response()->json([
                'message' => 'Device created successfully',
                'data' => [
                    'id' => rand(4, 100),
                    'name' => $request->input('name', 'New Device'),
                    'type' => $request->input('type', 'car'),
                    'status' => 'active',
                ],
            ], 201);
        });

        Route::get('/devices/{id}', function ($id) {
            return response()->json([
                'id' => $id,
                'name' => 'Vehicle ' . str_pad($id, 3, '0', STR_PAD_LEFT),
                'type' => 'car',
                'status' => 'active',
                'imei' => '123456789012345',
                'plate_number' => 'ABC-' . rand(100, 999),
                'last_position' => [
                    'lat' => 40.7128,
                    'lng' => -74.0060,
                    'speed' => 45,
                    'timestamp' => now()->subMinutes(2)->toISOString(),
                ],
            ]);
        });

        // Geofences
        Route::get('/geofences', function () {
            return response()->json([
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Office Zone',
                        'type' => 'circle',
                        'center' => ['lat' => 40.7128, 'lng' => -74.0060],
                        'radius' => 500,
                        'color' => '#3B82F6',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Warehouse Area',
                        'type' => 'polygon',
                        'coordinates' => [
                            ['lat' => 40.7589, 'lng' => -73.9851],
                            ['lat' => 40.7590, 'lng' => -73.9840],
                            ['lat' => 40.7580, 'lng' => -73.9840],
                            ['lat' => 40.7580, 'lng' => -73.9851],
                        ],
                        'color' => '#10B981',
                    ],
                ],
            ]);
        });

        Route::post('/geofences', function (Request $request) {
            return response()->json([
                'message' => 'Geofence created successfully',
                'data' => [
                    'id' => rand(3, 100),
                    'name' => $request->input('name', 'New Geofence'),
                    'type' => $request->input('type', 'circle'),
                ],
            ], 201);
        });

        // Positions / Telemetry
        Route::get('/positions/last', function () {
            return response()->json([
                'data' => [
                    [
                        'device_id' => 1,
                        'lat' => 40.7128,
                        'lng' => -74.0060,
                        'speed' => 45,
                        'heading' => 90,
                        'altitude' => 10,
                        'timestamp' => now()->subSeconds(30)->toISOString(),
                    ],
                    [
                        'device_id' => 2,
                        'lat' => 40.7589,
                        'lng' => -73.9851,
                        'speed' => 30,
                        'heading' => 180,
                        'altitude' => 15,
                        'timestamp' => now()->subSeconds(45)->toISOString(),
                    ],
                ],
            ]);
        });

        Route::get('/positions/history/{deviceId}', function ($deviceId) {
            $positions = [];
            for ($i = 0; $i < 10; $i++) {
                $positions[] = [
                    'lat' => 40.7128 + ($i * 0.001),
                    'lng' => -74.0060 + ($i * 0.001),
                    'speed' => rand(30, 60),
                    'timestamp' => now()->subMinutes($i * 5)->toISOString(),
                ];
            }
            
            return response()->json([
                'device_id' => $deviceId,
                'data' => $positions,
            ]);
        });

        // Alerts
        Route::get('/alerts', function () {
            return response()->json([
                'data' => [
                    [
                        'id' => 1,
                        'type' => 'geofence_exit',
                        'device_id' => 1,
                        'device_name' => 'Vehicle 001',
                        'message' => 'Vehicle exited Office Zone',
                        'severity' => 'warning',
                        'timestamp' => now()->subMinutes(15)->toISOString(),
                        'read' => false,
                    ],
                    [
                        'id' => 2,
                        'type' => 'speed_limit',
                        'device_id' => 2,
                        'device_name' => 'Truck 042',
                        'message' => 'Speed limit exceeded (85 km/h)',
                        'severity' => 'high',
                        'timestamp' => now()->subMinutes(30)->toISOString(),
                        'read' => false,
                    ],
                    [
                        'id' => 3,
                        'type' => 'idle',
                        'device_id' => 3,
                        'device_name' => 'Van 015',
                        'message' => 'Vehicle idle for 2 hours',
                        'severity' => 'info',
                        'timestamp' => now()->subHours(1)->toISOString(),
                        'read' => true,
                    ],
                ],
            ]);
        });

        Route::post('/alerts', function (Request $request) {
            return response()->json([
                'message' => 'Alert rule created successfully',
                'data' => [
                    'id' => rand(4, 100),
                    'type' => $request->input('type', 'geofence_entry'),
                    'enabled' => true,
                ],
            ], 201);
        });

        // Reports
        Route::post('/reports/generate', function (Request $request) {
            return response()->json([
                'message' => 'Report generation started',
                'report_id' => uniqid('report_'),
                'status' => 'processing',
            ], 202);
        });

        Route::get('/reports/{id}', function ($id) {
            return response()->json([
                'id' => $id,
                'type' => 'trip_report',
                'status' => 'completed',
                'generated_at' => now()->toISOString(),
                'data' => [
                    'total_trips' => 45,
                    'total_distance' => 1250.5,
                    'total_duration' => '25h 30m',
                    'fuel_consumed' => 125.5,
                ],
            ]);
        });
    });
});
