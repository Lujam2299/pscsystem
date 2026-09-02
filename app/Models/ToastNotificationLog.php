<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ToastNotificationLog extends Model
{
    public const TYPE_GENERIC = 'generic';

    public const TYPE_MESSAGE = 'message';

    public const TYPE_RH_HIRE_REQUEST = 'rh_hire_request';

    public const TYPE_RH_TERMINATION_REQUEST = 'rh_termination_request';

    private const ADMIN_ROLES = [
        'ADMIN',
        'ADMINISTRADOR',
    ];

    private const RH_ROLES = [
        'AUXILIAR RECURSOS HUMANOS',
        'AUXILIAR RH',
        'AUX RH',
        'RECURSOS HUMANOS',
        'JEFA RECURSOS HUMANOS',
    ];

    protected $fillable = [
        'type',
        'icon',
        'title',
        'text',
        'url',
        'key',
        'recipient_user_id',
        'actor_user_id',
        'audience',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public static function normalizedRole(?string $role): string
    {
        return strtoupper(trim((string) $role));
    }

    public static function isAdmin(User $user): bool
    {
        return in_array(self::normalizedRole($user->rol), self::ADMIN_ROLES, true);
    }

    public static function isRh(User $user): bool
    {
        $role = self::normalizedRole($user->rol);
        $altaRole = self::normalizedRole($user->solicitudAlta?->rol);
        $department = self::normalizedRole($user->solicitudAlta?->departamento);

        return in_array($role, self::RH_ROLES, true)
            || in_array($altaRole, self::RH_ROLES, true)
            || $department === 'RECURSOS HUMANOS';
    }

    public static function visibleFor(User $user): Builder
    {
        $query = self::query();

        if (self::isAdmin($user)) {
            return $query->where(function (Builder $query) use ($user) {
                $query->where('type', '!=', self::TYPE_MESSAGE)
                    ->orWhere('recipient_user_id', $user->id);
            });
        }

        if (self::isRh($user)) {
            return $query->where(function (Builder $query) use ($user) {
                $query->whereIn('type', [
                    self::TYPE_RH_HIRE_REQUEST,
                    self::TYPE_RH_TERMINATION_REQUEST,
                ])->orWhere(function (Builder $query) use ($user) {
                    $query->where('type', self::TYPE_MESSAGE)
                        ->where('recipient_user_id', $user->id);
                });
            });
        }

        return $query->where('recipient_user_id', $user->id);
    }

    public static function recentFor(User $user, int $limit = 15): Collection
    {
        if (! self::tableReady()) {
            return collect();
        }

        return self::visibleFor($user)
            ->latest()
            ->limit(100)
            ->get()
            ->unique(fn (self $notification) => $notification->key ?: 'notification:'.$notification->id)
            ->take($limit)
            ->values();
    }

    public static function unreadCountFor(User $user): int
    {
        if (! self::tableReady()) {
            return 0;
        }

        return self::visibleFor($user)
            ->where('recipient_user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public static function markReadFor(User $user): int
    {
        if (! self::tableReady()) {
            return 0;
        }

        return self::visibleFor($user)
            ->where('recipient_user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private static function tableReady(): bool
    {
        try {
            return Schema::hasTable('toast_notification_logs');
        } catch (\Throwable) {
            return false;
        }
    }
}
