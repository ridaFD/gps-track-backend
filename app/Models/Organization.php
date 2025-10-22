<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Organization extends Model
{
    use HasFactory, SoftDeletes, AsSource, Filterable;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'plan',
        'max_devices',
        'max_users',
        'settings',
        'is_active',
        'trial_ends_at',
        'subscription_ends_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organization) {
            if (empty($organization->slug)) {
                $organization->slug = Str::slug($organization->name);
            }
        });
    }

    /**
     * Relationships
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function geofences()
    {
        return $this->hasMany(Geofence::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now());
    }

    /**
     * Helper methods
     */
    public function isOnTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function hasSubscription()
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isFuture();
    }

    public function canAddDevice()
    {
        return $this->devices()->count() < $this->max_devices;
    }

    public function canAddUser()
    {
        return $this->users()->count() < $this->max_users;
    }

    public function isOwner(User $user)
    {
        return $this->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    public function isAdmin(User $user)
    {
        return $this->users()
            ->wherePivot('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }
}
