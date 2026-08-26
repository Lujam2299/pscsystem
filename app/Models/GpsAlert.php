<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GpsAlert extends Model
{
    protected $fillable = [
        'traccar_event_id',
        'device_id',
        'position_id',
        'geofence_id',
        'type',
        'priority',
        'event_time',
        'attributes',
    ];

    protected function casts(): array
    {
        return [
            'event_time' => 'immutable_datetime',
            'attributes' => 'array',
        ];
    }

    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'gps_alert_reads')
            ->withPivot('read_at');
    }
}
