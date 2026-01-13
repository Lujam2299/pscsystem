<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'is_group'];

    public function users()
    {
        return $this->belongsToMany(
            User::class,           // modelo relacionado
            'conversation_user',   // tabla pivote
            'conversation_id',     // clave foránea local (en la pivote)
            'api_user_id'          // clave foránea del usuario (en la pivote)
        )
        ->withPivot(['last_read_at', 'unread_count'])
        ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }


}

