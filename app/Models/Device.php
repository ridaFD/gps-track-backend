<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Device extends Model
{
    use HasFactory, SoftDeletes, AsSource, Filterable;

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
}
