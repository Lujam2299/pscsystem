<?php

namespace App\Observers;

use App\Models\BuzonQueja;
use App\Services\RealtimeToast;
use Illuminate\Support\Str;

class BuzonQuejaObserver
{
    private const ROLES = ['JEFE', 'ADMIN', 'ADMINISTRADOR'];

    public function created(BuzonQueja $queja): void
    {
        $queja->loadMissing('user');

        RealtimeToast::toRoles(self::ROLES, [
            'icon' => 'info',
            'title' => 'Nueva entrada en el buzón',
            'text' => ($queja->user?->name ?? 'Usuario') . ': ' . Str::limit($queja->asunto, 100),
            'url' => route('admin.verBuzon'),
            'key' => 'buzon:' . $queja->id,
        ]);
    }
}
