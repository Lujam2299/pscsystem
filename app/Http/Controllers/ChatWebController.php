<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatWebController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $conversaciones = $user->conversations()->with(['users', 'latestMessage'])->get();

        return view('chat.index', compact('conversaciones'));
    }

    public function show(Conversation $conversation)
    {
        abort_unless($conversation->users->contains(Auth::id()), 403);

        $conversation->load(['messages.user', 'users']);

        return view('chat.show', compact('conversation'));
    }

    public function storeMensaje(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'body' => 'required|string|max:1000',
        ]);

        $mensaje = Message::create([
            'conversation_id' => $request->conversation_id,
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        // (más adelante aquí se puede emitir evento WebSocket)

        return redirect()->route('mensajes.show', $mensaje->conversation_id);
    }

    public function crear()
{
    $usuarios = User::where('id', '!=', auth()->id())->get();

    return view('chat.crear', compact('usuarios'));
}

public function storeConversacion(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id'
    ]);

    $authUser = auth()->user();
    $otherUserId = $request->user_id;

    $conversation = $authUser->conversations()
        ->where('is_group', false)
        ->whereHas('users', function ($q) use ($otherUserId) {
            $q->where('user_id', $otherUserId);
        })
        ->first();

    if (!$conversation) {
        $conversation = \App\Models\Conversation::create(['is_group' => false]);

        $conversation->users()->attach([$authUser->id, $otherUserId]);
    }

    return redirect()->route('mensajes.show', $conversation);
}
}
