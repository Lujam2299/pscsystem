<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
        'parent_id',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function users()
{
    return $this->belongsToMany(
        User::class,            // Modelo relacionado
        'conversation_user',    // Nombre de la tabla pivote
        'conversation_id',      // Foreign key en la tabla pivote hacia este modelo (Conversation)
        'user_id'           // Foreign key en la tabla pivote hacia User
    )
    ->withPivot('last_read_at')
    ->withTimestamps();
}
    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id');
    }
public function user()
{
    return $this->belongsTo(User::class, 'user_id'); // Asegúrate de que 'user_id' sea la clave correcta
}
}
