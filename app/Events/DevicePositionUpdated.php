<?php

namespace App\Events;

use App\Models\Position;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DevicePositionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $position;
    public $deviceId;

    /**
     * Create a new event instance.
     */
    public function __construct(Position $position)
    {
        $this->position = $position;
        $this->deviceId = $position->device_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('devices'),
            new Channel("device.{$this->deviceId}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'position.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'device_id' => $this->position->device_id,
            'latitude' => $this->position->latitude,
            'longitude' => $this->position->longitude,
            'speed' => $this->position->speed,
            'heading' => $this->position->heading,
            'altitude' => $this->position->altitude,
            'ignition' => $this->position->ignition,
            'fuel_level' => $this->position->fuel_level,
            'device_time' => $this->position->device_time->toIso8601String(),
            'created_at' => $this->position->created_at->toIso8601String(),
        ];
    }
}
