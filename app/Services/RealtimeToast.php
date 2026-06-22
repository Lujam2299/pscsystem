<?php

namespace App\Services;

use App\Events\ToastNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RealtimeToast
{
    public static function toRoles(array $roles, array $payload, ?int $excludeUserId = null): void
    {
        $normalizedRoles = collect($roles)
            ->map(fn ($role) => strtoupper(trim((string) $role)))
            ->filter()
            ->unique()
            ->values();

        if ($normalizedRoles->isEmpty()) {
            return;
        }

        $placeholders = $normalizedRoles->map(fn () => '?')->implode(',');
        $userIds = User::query()
            ->whereRaw("UPPER(TRIM(rol)) IN ({$placeholders})", $normalizedRoles->all())
            ->pluck('id');

        self::toUsers($userIds, $payload, $excludeUserId);
    }

    public static function toUsers(iterable $userIds, array $payload, ?int $excludeUserId = null): void
    {
        $recipients = collect($userIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && $id !== $excludeUserId)
            ->unique()
            ->values()
            ->all();

        if (empty($recipients)) {
            return;
        }

        $payload = array_merge([
            'icon' => 'info',
            'title' => 'Nueva notificación',
            'text' => '',
            'url' => null,
            'key' => null,
        ], $payload);

        try {
            broadcast(new ToastNotification($recipients, $payload));
        } catch (\Throwable $exception) {
            Log::warning('No se pudo emitir un toast en tiempo real.', [
                'error' => $exception->getMessage(),
                'recipients' => $recipients,
                'key' => $payload['key'],
            ]);
        }
    }
}
