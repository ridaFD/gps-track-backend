<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Geofence extends Model
{
    use HasFactory, SoftDeletes, AsSource, Filterable, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'type',
        'center_lat',
        'center_lng',
        'radius',
        'coordinates',
        'color',
        'active',
        'alert_on_enter',
        'alert_on_exit',
        'user_id',
    ];

    protected $casts = [
        'center_lat' => 'decimal:7',
        'center_lng' => 'decimal:7',
        'coordinates' => 'array',
        'active' => 'boolean',
        'alert_on_enter' => 'boolean',
        'alert_on_exit' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Activity Log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'type', 'center_lat', 'center_lng', 'radius', 'active', 'alert_on_enter', 'alert_on_exit'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Geofence {$eventName}");
    }
}
