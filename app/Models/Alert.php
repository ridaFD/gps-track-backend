<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Alert extends Model
{
    use HasFactory, AsSource, Filterable, LogsActivity;

    protected $fillable = [
        'organization_id',
        'device_id',
        'geofence_id',
        'type',
        'severity',
        'message',
        'data',
        'read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function geofence()
    {
        return $this->belongsTo(Geofence::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Boot method for global scopes
     */
    protected static function booted()
    {
        // Global scope to automatically filter by current organization
        static::addGlobalScope('organization', function ($query) {
            if (auth()->check() && auth()->user()->currentOrganization()) {
                $query->where('organization_id', auth()->user()->currentOrganization()->id);
            }
        });
    }

    /**
     * Activity Log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'severity', 'message', 'read'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Alert {$eventName}");
    }
}
