<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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

    protected static function booted()
    {
        static::created(function ($message) {
            $message->conversation->touch(); // Actualiza updated_at de la conversación

            // Incrementar unread_count para otros usuarios en la conversación
            $otherUsers = $message->conversation->users->where('id', '!=', $message->user_id);

            foreach ($otherUsers as $user) {
                $conversationUser = DB::table('conversation_user')
                    ->where('conversation_id', $message->conversation_id)
                    ->where('api_user_id', $user->id)
                    ->first();

                if ($conversationUser) {
                    DB::table('conversation_user')
                        ->where('conversation_id', $message->conversation_id)
                        ->where('api_user_id', $user->id)
                        ->increment('unread_count');

                    \Log::info("Incrementado unread_count para user: {$user->id}, conv: {$message->conversation_id}");
                } else {
                    \Log::info("No encontrado registro en conversation_user para user: {$user->id}, conv: {$message->conversation_id}");
                }
            }
        });
    }
}
