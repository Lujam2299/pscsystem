<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reingreso extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'numero_reingreso',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
        'numero_reingreso' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
