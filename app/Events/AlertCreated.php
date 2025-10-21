<?php

namespace App\Events;

use App\Models\Alert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $alert;

    /**
     * Create a new event instance.
     */
    public function __construct(Alert $alert)
    {
        $this->alert = $alert;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('alerts'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'alert.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->alert->id,
            'device_id' => $this->alert->device_id,
            'device_name' => $this->alert->device?->name,
            'geofence_id' => $this->alert->geofence_id,
            'geofence_name' => $this->alert->geofence?->name,
            'type' => $this->alert->type,
            'severity' => $this->alert->severity,
            'message' => $this->alert->message,
            'data' => $this->alert->data,
            'created_at' => $this->alert->created_at->toIso8601String(),
        ];
    }
}
