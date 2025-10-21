<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Position extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $fillable = [
        'device_id',
        'latitude',
        'longitude',
        'altitude',
        'speed',
        'heading',
        'satellites',
        'accuracy',
        'odometer',
        'fuel_level',
        'battery_level',
        'ignition',
        'address',
        'raw_data',
        'device_time',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'altitude' => 'decimal:2',
        'speed' => 'decimal:2',
        'ignition' => 'boolean',
        'raw_data' => 'array',
        'device_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
