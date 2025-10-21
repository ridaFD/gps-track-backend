<?php

namespace App\Jobs;

use App\Events\AlertCreated as AlertCreatedEvent;
use App\Models\Position;
use App\Models\Alert;
use App\Models\Geofence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class EvaluateAlertRulesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $position;

    /**
     * Create a new job instance.
     */
    public function __construct(Position $position)
    {
        $this->position = $position;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Check speed limit violations
        $this->checkSpeedLimit();

        // 2. Check geofence entry/exit
        $this->checkGeofences();

        // 3. Check idle time
        $this->checkIdleTime();

        // 4. Check low battery
        $this->checkLowBattery();
    }

    /**
     * Check if device exceeded speed limit
     */
    protected function checkSpeedLimit(): void
    {
        $speedLimit = 120; // km/h - should be configurable per device/rule

        if ($this->position->speed > $speedLimit) {
            $alert = Alert::create([
                'device_id' => $this->position->device_id,
                'type' => 'speed_limit',
                'severity' => 'high',
                'message' => "Device exceeded speed limit: {$this->position->speed} km/h (limit: {$speedLimit} km/h)",
                'data' => [
                    'speed' => $this->position->speed,
                    'speed_limit' => $speedLimit,
                    'latitude' => $this->position->latitude,
                    'longitude' => $this->position->longitude,
                ],
            ]);

            broadcast(new AlertCreatedEvent($alert))->toOthers();
        }
    }

    /**
     * Check geofence entry/exit
     */
    protected function checkGeofences(): void
    {
        // Get all active geofences
        $geofences = Geofence::where('active', true)->get();

        foreach ($geofences as $geofence) {
            if ($geofence->type === 'circle') {
                $isInside = $this->isPointInCircle(
                    $this->position->latitude,
                    $this->position->longitude,
                    $geofence->center_lat,
                    $geofence->center_lng,
                    $geofence->radius
                );

                if ($isInside && $geofence->alert_on_enter) {
                    // Check if device was previously outside (simple check - should use Redis state)
                    $this->createGeofenceAlert($geofence, 'geofence_entry');
                }
            }
            // TODO: Add polygon and rectangle checks using PostGIS when migrated
        }
    }

    /**
     * Check if point is inside circle geofence
     */
    protected function isPointInCircle($lat, $lng, $centerLat, $centerLng, $radiusMeters): bool
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat - $centerLat);
        $dLng = deg2rad($lng - $centerLng);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($centerLat)) * cos(deg2rad($lat)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance <= $radiusMeters;
    }

    /**
     * Create geofence alert
     */
    protected function createGeofenceAlert(Geofence $geofence, string $type): void
    {
        $alert = Alert::create([
            'device_id' => $this->position->device_id,
            'geofence_id' => $geofence->id,
            'type' => $type,
            'severity' => 'warning',
            'message' => "Device {$type} geofence: {$geofence->name}",
            'data' => [
                'geofence_name' => $geofence->name,
                'latitude' => $this->position->latitude,
                'longitude' => $this->position->longitude,
            ],
        ]);

        broadcast(new AlertCreatedEvent($alert))->toOthers();
    }

    /**
     * Check idle time (speed 0 for extended period)
     */
    protected function checkIdleTime(): void
    {
        if ($this->position->speed == 0 && $this->position->ignition) {
            // Check if device has been idle for > 30 minutes
            $idleThreshold = now()->subMinutes(30);

            $recentPositions = Position::where('device_id', $this->position->device_id)
                ->where('device_time', '>=', $idleThreshold)
                ->where('speed', 0)
                ->where('ignition', true)
                ->count();

            if ($recentPositions >= 3) { // At least 3 position reports with speed 0
                $alert = Alert::create([
                    'device_id' => $this->position->device_id,
                    'type' => 'idle',
                    'severity' => 'warning',
                    'message' => 'Device has been idle with engine running for 30+ minutes',
                    'data' => [
                        'idle_duration' => 30,
                        'latitude' => $this->position->latitude,
                        'longitude' => $this->position->longitude,
                    ],
                ]);

                broadcast(new AlertCreatedEvent($alert))->toOthers();
            }
        }
    }

    /**
     * Check low battery
     */
    protected function checkLowBattery(): void
    {
        if ($this->position->battery_level !== null && $this->position->battery_level < 20) {
            $alert = Alert::create([
                'device_id' => $this->position->device_id,
                'type' => 'low_battery',
                'severity' => 'high',
                'message' => "Device battery is low: {$this->position->battery_level}%",
                'data' => [
                    'battery_level' => $this->position->battery_level,
                    'latitude' => $this->position->latitude,
                    'longitude' => $this->position->longitude,
                ],
            ]);

            broadcast(new AlertCreatedEvent($alert))->toOthers();
        }
    }
}
