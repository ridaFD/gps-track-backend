<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Device;
use App\Models\Position;
use App\Models\Geofence;
use App\Models\Alert;
use App\Jobs\GenerateReportJob;
use Spatie\Activitylog\Models\Activity;

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
            'database' => 'connected',
        ]);
    });

    // Authentication endpoints (Sanctum)
    Route::post('/login', function (Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user || !\Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Create token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    });

    Route::post('/register', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Hash::make($request->password),
        ]);

        // Create token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    });

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        
        // Logout endpoint
        Route::post('/logout', function (Request $request) {
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'message' => 'Logged out successfully'
            ]);
        });
        
        // User info
        Route::get('/user', function (Request $request) {
            return response()->json([
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'company' => 'GPS Tracking Inc.',
                'role' => 'admin',
            ]);
        });

        // Dashboard statistics
        Route::get('/statistics/dashboard', function () {
            $totalAssets = Device::count();
            $activeAssets = Device::where('status', 'active')->count();
            $geofences = Geofence::where('active', true)->count();
            $alertsToday = Alert::whereDate('created_at', today())->count();
            
            // Calculate distance today (sum of speeds * time intervals)
            $distanceToday = Position::whereDate('created_at', today())->sum('speed') / 10;
            
            $activeTrips = Device::where('status', 'active')
                ->whereHas('lastPosition', function($query) {
                    $query->where('speed', '>', 0);
                })->count();

            return response()->json([
                'total_assets' => $totalAssets,
                'active_assets' => $activeAssets,
                'geofences' => $geofences,
                'alerts_today' => $alertsToday,
                'distance_today' => round($distanceToday, 1),
                'active_trips' => $activeTrips,
            ]);
        });

        // Search Devices
        Route::get('/devices/search', function (Request $request) {
            $query = $request->input('q', '');
            
            if (empty($query)) {
                return response()->json([
                    'data' => [],
                    'message' => 'Please provide a search query'
                ]);
            }
            
            $devices = Device::search($query)
                ->query(fn ($builder) => $builder->with('lastPosition'))
                ->get()
                ->map(function($device) {
                    $lastPos = $device->lastPosition;
                    return [
                        'id' => $device->id,
                        'name' => $device->name,
                        'type' => $device->type,
                        'status' => $device->status,
                        'plate_number' => $device->plate_number,
                        'imei' => $device->imei,
                        'last_position' => $lastPos ? [
                            'lat' => $lastPos->latitude,
                            'lng' => $lastPos->longitude,
                            'speed' => $lastPos->speed,
                            'timestamp' => $lastPos->device_time,
                        ] : null,
                    ];
                });
            
            return response()->json([
                'data' => $devices,
                'count' => $devices->count(),
                'query' => $query,
            ]);
        });

        // Devices/Assets
        Route::get('/devices', function (Request $request) {
            $devices = Device::with('lastPosition')->get()->map(function($device) {
                $lastPos = $device->lastPosition;
                return [
                    'id' => $device->id,
                    'name' => $device->name,
                    'type' => $device->type,
                    'status' => $device->status,
                    'plate_number' => $device->plate_number,
                    'last_position' => $lastPos ? [
                        'lat' => (float) $lastPos->latitude,
                        'lng' => (float) $lastPos->longitude,
                        'speed' => (float) $lastPos->speed,
                        'timestamp' => $lastPos->device_time->toISOString(),
                    ] : null,
                ];
            });

            return response()->json([
                'data' => $devices,
                'meta' => [
                    'total' => $devices->count(),
                    'per_page' => 15,
                    'current_page' => 1,
                ],
            ]);
        });

        Route::post('/devices', function (Request $request) {
            $device = Device::create([
                'name' => $request->input('name'),
                'type' => $request->input('type', 'car'),
                'status' => 'active',
                'plate_number' => $request->input('plate_number'),
            ]);

            return response()->json([
                'message' => 'Device created successfully',
                'data' => $device,
            ], 201);
        });

        Route::get('/devices/{id}', function ($id) {
            $device = Device::with('lastPosition')->findOrFail($id);
            $lastPos = $device->lastPosition;
            
            return response()->json([
                'id' => $device->id,
                'name' => $device->name,
                'type' => $device->type,
                'status' => $device->status,
                'imei' => $device->imei,
                'plate_number' => $device->plate_number,
                'model' => $device->model,
                'color' => $device->color,
                'year' => $device->year,
                'driver_name' => $device->driver_name,
                'driver_phone' => $device->driver_phone,
                'last_position' => $lastPos ? [
                    'lat' => (float) $lastPos->latitude,
                    'lng' => (float) $lastPos->longitude,
                    'speed' => (float) $lastPos->speed,
                    'heading' => $lastPos->heading,
                    'timestamp' => $lastPos->device_time->toISOString(),
                ] : null,
            ]);
        });

        Route::put('/devices/{id}', function (Request $request, $id) {
            $device = Device::findOrFail($id);
            $device->update($request->only(['name', 'type', 'status', 'plate_number', 'driver_name', 'driver_phone']));
            
            return response()->json([
                'message' => 'Device updated successfully',
                'data' => $device,
            ]);
        });

        Route::delete('/devices/{id}', function ($id) {
            $device = Device::findOrFail($id);
            $device->delete();
            
            return response()->json([
                'message' => 'Device deleted successfully',
            ]);
        });

        // Geofences
        Route::get('/geofences', function () {
            $geofences = Geofence::where('active', true)->get()->map(function($geofence) {
                $data = [
                    'id' => $geofence->id,
                    'name' => $geofence->name,
                    'description' => $geofence->description,
                    'type' => $geofence->type,
                    'color' => $geofence->color,
                    'active' => $geofence->active,
                ];

                if ($geofence->type === 'circle') {
                    $data['center'] = [
                        'lat' => (float) $geofence->center_lat,
                        'lng' => (float) $geofence->center_lng,
                    ];
                    $data['radius'] = $geofence->radius;
                } else {
                    $data['coordinates'] = $geofence->coordinates;
                }

                return $data;
            });

            return response()->json(['data' => $geofences]);
        });

        Route::post('/geofences', function (Request $request) {
            $geofence = Geofence::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'type' => $request->input('type', 'circle'),
                'center_lat' => $request->input('center_lat'),
                'center_lng' => $request->input('center_lng'),
                'radius' => $request->input('radius'),
                'coordinates' => $request->input('coordinates'),
                'color' => $request->input('color', '#3B82F6'),
                'active' => true,
            ]);

            return response()->json([
                'message' => 'Geofence created successfully',
                'data' => $geofence,
            ], 201);
        });

        Route::put('/geofences/{id}', function (Request $request, $id) {
            $geofence = Geofence::findOrFail($id);
            $geofence->update($request->all());
            
            return response()->json([
                'message' => 'Geofence updated successfully',
                'data' => $geofence,
            ]);
        });

        Route::delete('/geofences/{id}', function ($id) {
            $geofence = Geofence::findOrFail($id);
            $geofence->delete();
            
            return response()->json([
                'message' => 'Geofence deleted successfully',
            ]);
        });

        // Positions / Telemetry
        Route::get('/positions/last', function (Request $request) {
            $deviceId = $request->input('device_id');
            
            $query = Position::with('device')
                ->whereHas('device', function($q) {
                    $q->where('status', 'active');
                });

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            $positions = $query->get()
                ->groupBy('device_id')
                ->map(function($devicePositions) {
                    return $devicePositions->sortByDesc('device_time')->first();
                })
                ->values()
                ->map(function($position) {
                    return [
                        'device_id' => $position->device_id,
                        'device_name' => $position->device->name,
                        'lat' => (float) $position->latitude,
                        'lng' => (float) $position->longitude,
                        'speed' => (float) $position->speed,
                        'heading' => $position->heading,
                        'altitude' => (float) $position->altitude,
                        'timestamp' => $position->device_time->toISOString(),
                    ];
                });

            return response()->json(['data' => $positions]);
        });

        Route::get('/positions/history/{deviceId}', function ($deviceId, Request $request) {
            $limit = $request->input('limit', 100);
            $from = $request->input('from');
            $to = $request->input('to');

            $query = Position::where('device_id', $deviceId)
                ->orderBy('device_time', 'desc')
                ->limit($limit);

            if ($from) {
                $query->where('device_time', '>=', $from);
            }
            if ($to) {
                $query->where('device_time', '<=', $to);
            }

            $positions = $query->get()->map(function($position) {
                return [
                    'lat' => (float) $position->latitude,
                    'lng' => (float) $position->longitude,
                    'speed' => (float) $position->speed,
                    'heading' => $position->heading,
                    'altitude' => (float) $position->altitude,
                    'timestamp' => $position->device_time->toISOString(),
                ];
            });

            return response()->json([
                'device_id' => (int) $deviceId,
                'data' => $positions,
            ]);
        });

        // Alerts
        Route::get('/alerts', function (Request $request) {
            $limit = $request->input('limit', 50);
            $unreadOnly = $request->input('unread_only', false);

            $query = Alert::with(['device', 'geofence'])
                ->orderBy('created_at', 'desc')
                ->limit($limit);

            if ($unreadOnly) {
                $query->where('read', false);
            }

            $alerts = $query->get()->map(function($alert) {
                return [
                    'id' => $alert->id,
                    'type' => $alert->type,
                    'device_id' => $alert->device_id,
                    'device_name' => $alert->device->name,
                    'geofence_id' => $alert->geofence_id,
                    'geofence_name' => $alert->geofence?->name,
                    'message' => $alert->message,
                    'severity' => $alert->severity,
                    'data' => $alert->data,
                    'read' => $alert->read,
                    'read_at' => $alert->read_at?->toISOString(),
                    'timestamp' => $alert->created_at->toISOString(),
                ];
            });

            return response()->json(['data' => $alerts]);
        });

        Route::post('/alerts', function (Request $request) {
            $alert = Alert::create([
                'device_id' => $request->input('device_id'),
                'geofence_id' => $request->input('geofence_id'),
                'type' => $request->input('type'),
                'severity' => $request->input('severity', 'info'),
                'message' => $request->input('message'),
                'data' => $request->input('data'),
                'read' => false,
            ]);

            return response()->json([
                'message' => 'Alert created successfully',
                'data' => $alert,
            ], 201);
        });

        Route::patch('/alerts/{id}/read', function ($id) {
            $alert = Alert::findOrFail($id);
            $alert->update([
                'read' => true,
                'read_at' => now(),
            ]);

            return response()->json([
                'message' => 'Alert marked as read',
                'data' => $alert,
            ]);
        });

        Route::post('/alerts/read-all', function () {
            Alert::where('read', false)->update([
                'read' => true,
                'read_at' => now(),
            ]);

            return response()->json([
                'message' => 'All alerts marked as read',
            ]);
        });

        // Reports - Generate
        Route::post('/reports/generate', function (Request $request) {
            $validated = $request->validate([
                'type' => 'required|in:devices,trips,alerts',
                'from' => 'nullable|date',
                'to' => 'nullable|date',
                'device_id' => 'nullable|integer',
            ]);

            // Dispatch report generation job
            GenerateReportJob::dispatch(
                $validated['type'],
                $request->only(['from', 'to', 'device_id'])
            );

            return response()->json([
                'message' => 'Report generation started',
                'type' => $validated['type'],
                'status' => 'processing',
            ], 202);
        });

        // Reports - List all available reports
        Route::get('/reports', function () {
            $reportsPath = storage_path('app/reports');
            
            if (!is_dir($reportsPath)) {
                return response()->json(['reports' => []]);
            }

            $files = array_diff(scandir($reportsPath, SCANDIR_SORT_DESCENDING), ['.', '..']);
            
            $reports = array_map(function($file) use ($reportsPath) {
                $filePath = $reportsPath . '/' . $file;
                return [
                    'filename' => $file,
                    'size' => filesize($filePath),
                    'created_at' => filemtime($filePath),
                    'download_url' => "/api/v1/reports/download/" . urlencode($file),
                ];
            }, $files);

            return response()->json([
                'reports' => array_values($reports),
                'count' => count($reports),
            ]);
        });

        // Reports - Download specific report
        Route::get('/reports/download/{filename}', function ($filename) {
            $path = storage_path('app/reports/' . $filename);
            
            if (!file_exists($path)) {
                return response()->json(['error' => 'Report not found'], 404);
            }
            
            return response()->download($path);
        });

        // Reports - Delete specific report
        Route::delete('/reports/delete/{filename}', function ($filename) {
            $path = storage_path('app/reports/' . $filename);
            
            if (!file_exists($path)) {
                return response()->json(['error' => 'Report not found'], 404);
            }
            
            // Delete the file
            if (unlink($path)) {
                return response()->json([
                    'message' => 'Report deleted successfully',
                    'filename' => $filename,
                ], 200);
            }
            
            return response()->json(['error' => 'Failed to delete report'], 500);
        });

        // Reports - Get report status (for polling)
        Route::get('/reports/status/{id}', function ($id) {
            return response()->json([
                'id' => $id,
                'status' => 'completed',
                'generated_at' => now()->toISOString(),
            ]);
        });

        // Activity Log - Get all activities
        Route::get('/activity-log', function (Request $request) {
            $query = Activity::with(['causer', 'subject'])
                ->orderBy('created_at', 'desc');
            
            // Filter by model type
            if ($request->has('model_type')) {
                $query->where('subject_type', $request->model_type);
            }
            
            // Filter by model id
            if ($request->has('model_id')) {
                $query->where('subject_id', $request->model_id);
            }
            
            // Filter by causer (user)
            if ($request->has('user_id')) {
                $query->where('causer_id', $request->user_id);
            }
            
            // Limit
            $limit = $request->input('limit', 50);
            $activities = $query->limit($limit)->get();
            
            return response()->json([
                'data' => $activities->map(function($activity) {
                    return [
                        'id' => $activity->id,
                        'description' => $activity->description,
                        'subject_type' => class_basename($activity->subject_type),
                        'subject_id' => $activity->subject_id,
                        'causer_name' => $activity->causer->name ?? 'System',
                        'causer_email' => $activity->causer->email ?? null,
                        'properties' => $activity->properties,
                        'created_at' => $activity->created_at->toISOString(),
                    ];
                }),
                'count' => $activities->count(),
            ]);
        });

        // Activity Log - Get activities for specific model
        Route::get('/activity-log/{modelType}/{modelId}', function ($modelType, $modelId) {
            $modelClass = 'App\\Models\\' . ucfirst($modelType);
            
            if (!class_exists($modelClass)) {
                return response()->json(['error' => 'Invalid model type'], 400);
            }
            
            $activities = Activity::where('subject_type', $modelClass)
                ->where('subject_id', $modelId)
                ->with('causer')
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'data' => $activities->map(function($activity) {
                    return [
                        'id' => $activity->id,
                        'description' => $activity->description,
                        'causer_name' => $activity->causer->name ?? 'System',
                        'properties' => $activity->properties,
                        'created_at' => $activity->created_at->toISOString(),
                    ];
                }),
                'count' => $activities->count(),
            ]);
        });
    });
});
