<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GpsSpeedLimit extends Model
{
    protected $fillable = [
        'device_id',
        'speed_limit_kmh',
        'tolerance_seconds',
        'active',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'speed_limit_kmh' => 'decimal:1',
            'tolerance_seconds' => 'integer',
            'active' => 'boolean',
        ];
    }
}
