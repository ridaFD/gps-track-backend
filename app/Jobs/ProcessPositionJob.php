<?php

namespace App\Jobs;

use App\Events\DevicePositionUpdated;
use App\Models\Position;
use App\Models\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ProcessPositionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $positionData;
    public $deviceId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $positionData, int $deviceId)
    {
        $this->positionData = $positionData;
        $this->deviceId = $deviceId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Store position in database
        $position = Position::create([
            'device_id' => $this->deviceId,
            'latitude' => $this->positionData['latitude'],
            'longitude' => $this->positionData['longitude'],
            'altitude' => $this->positionData['altitude'] ?? null,
            'speed' => $this->positionData['speed'] ?? null,
            'heading' => $this->positionData['heading'] ?? null,
            'satellites' => $this->positionData['satellites'] ?? null,
            'accuracy' => $this->positionData['accuracy'] ?? null,
            'odometer' => $this->positionData['odometer'] ?? null,
            'fuel_level' => $this->positionData['fuel_level'] ?? null,
            'battery_level' => $this->positionData['battery_level'] ?? null,
            'ignition' => $this->positionData['ignition'] ?? null,
            'address' => $this->positionData['address'] ?? null,
            'raw_data' => $this->positionData['raw_data'] ?? null,
            'device_time' => $this->positionData['device_time'] ?? now(),
        ]);

        // Update last known position in Redis cache for fast access
        Cache::put("device.{$this->deviceId}.last_position", [
            'latitude' => $position->latitude,
            'longitude' => $position->longitude,
            'speed' => $position->speed,
            'heading' => $position->heading,
            'ignition' => $position->ignition,
            'device_time' => $position->device_time->toIso8601String(),
        ], now()->addHours(24));

        // Update device last_updated timestamp
        Device::where('id', $this->deviceId)->update([
            'updated_at' => now(),
        ]);

        // Broadcast position update for real-time map
        broadcast(new DevicePositionUpdated($position))->toOthers();

        // Dispatch alert rules evaluation
        EvaluateAlertRulesJob::dispatch($position);
    }
}
