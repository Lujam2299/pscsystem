<?php

namespace App\Support\Custodios;

final class MissionStatus
{
    public const PENDING = 'Pendiente';
    public const SCHEDULED = 'Programada';
    public const IN_PROGRESS = 'En Curso';
    public const REPORTED = 'Reportada';
    public const FINISHED = 'Finalizada';
    public const CANCELLED = 'Cancelada';

    /** @return array<int, string> */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::SCHEDULED,
            self::IN_PROGRESS,
            self::REPORTED,
            self::FINISHED,
            self::CANCELLED,
        ];
    }

    public static function normalize(?string $status): string
    {
        $key = strtoupper(trim(preg_replace('/\s+/u', ' ', (string) $status) ?? ''));

        return match ($key) {
            'PROGRAMADA' => self::SCHEDULED,
            'ACTIVA', 'EN CURSO' => self::IN_PROGRESS,
            'REPORTADA' => self::REPORTED,
            'COMPLETADA', 'TERMINADA', 'FINALIZADA' => self::FINISHED,
            'CANCELADA' => self::CANCELLED,
            'PENDIENTE' => self::PENDING,
            default => self::PENDING,
        };
    }

    /** @return array<int, string> */
    public static function transitionsFrom(?string $status): array
    {
        return match (self::normalize($status)) {
            self::PENDING => [self::SCHEDULED, self::CANCELLED],
            self::SCHEDULED => [self::IN_PROGRESS, self::CANCELLED],
            self::IN_PROGRESS => [self::REPORTED, self::CANCELLED],
            self::REPORTED => [self::FINISHED, self::IN_PROGRESS],
            self::FINISHED, self::CANCELLED => [],
        };
    }

    public static function canTransition(?string $from, ?string $to): bool
    {
        return in_array(self::normalize($to), self::transitionsFrom($from), true);
    }

    public static function isCancelled(?string $status): bool
    {
        return self::normalize($status) === self::CANCELLED;
    }

    public static function tone(?string $status): string
    {
        return match (self::normalize($status)) {
            self::PENDING => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200',
            self::SCHEDULED => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200',
            self::IN_PROGRESS => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200',
            self::REPORTED => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-200',
            self::FINISHED => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200',
            self::CANCELLED => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200',
        };
    }
}
