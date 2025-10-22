<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Device extends Model
{
    use HasFactory, SoftDeletes, AsSource, Filterable, Searchable, LogsActivity;

    protected $fillable = [
        'name',
        'imei',
        'type',
        'status',
        'plate_number',
        'model',
        'color',
        'year',
        'driver_name',
        'driver_phone',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function lastPosition()
    {
        return $this->hasOne(Position::class)->latestOfMany('device_time');
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'imei' => $this->imei,
            'type' => $this->type,
            'plate_number' => $this->plate_number,
            'model' => $this->model,
            'driver_name' => $this->driver_name,
        ];
    }

    /**
     * Activity Log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'imei', 'type', 'status', 'plate_number', 'model', 'driver_name', 'driver_phone'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Device {$eventName}");
    }
}
