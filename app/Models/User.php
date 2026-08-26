<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Authorization\RolePermissionMap;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'sol_alta_id',
        'sol_docs_id',
        'num_empleado',
        'email',
        'password',
        'fecha_ingreso',
        'punto',
        'rol',
        'estatus',
        'empresa',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasPermission(string $permission): bool
    {
        return RolePermissionMap::allows($this, $permission);
    }
    public function solicitudBajas()
    {
        return $this->hasMany(SolicitudBajas::class);
    }
    public function documentacionAltas()
    {
        return $this->hasOne(DocumentacionAltas::class, 'id', 'sol_docs_id');
    }
    public function solicitudAlta()
    {
        return $this->hasOne(SolicitudAlta::class, 'id', 'sol_alta_id');
    }

    public function solicitarVacacionesForm()
    {
        return view('user.solicitarVacacionesForm');
    }

    public function subpunto()
    {
        return $this->belongsTo(Subpunto::class);
    }

    public function subpuntosSupervisados()
    {
        return $this->belongsToMany(Subpunto::class, 'supervisorpuntos', 'supervisor_id', 'subpunto_id');
    }

    public function conversations()
    {
        return $this->belongsToMany(
            Conversation::class,
            'conversation_user',        // nombre tabla pivote
            'api_user_id',              // FK hacia este modelo (User)
            'conversation_id'           // FK hacia Conversation
        )
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function siniestros()
    {
        return $this->belongsToMany(\App\Models\Siniestro::class, 'siniestro_user', 'user_id', 'siniestro_id');
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function realtimePositions()
    {
        return $this->hasMany(\App\Models\RealtimePosition::class, 'user_id');
    }
}
