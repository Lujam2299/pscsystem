<?php

namespace App\Support\Authorization;

use App\Models\User;
use Illuminate\Support\Str;

final class RoleNormalizer
{
    public const ADMIN = 'admin';
    public const ACCOUNTING = 'accounting';
    public const PAYROLL = 'payroll';
    public const HUMAN_RESOURCES = 'human-resources';
    public const OPERATIONS = 'operations';
    public const IMSS = 'imss';
    public const MONITORING = 'monitoring';
    public const LEGAL = 'legal';
    public const SUPERVISOR = 'supervisor';
    public const CUSTODIAN = 'custodian';
    public const USER = 'user';

    public static function for(User $user): string
    {
        $role = self::normalize($user->rol);
        $requestRole = self::normalize($user->solicitudAlta?->rol);
        $department = self::normalize($user->solicitudAlta?->departamento);
        $candidates = array_filter([$role, $requestRole, $department]);

        if (in_array($role, ['admin', 'administrador'], true)) {
            return self::ADMIN;
        }

        if (self::containsAny($candidates, ['auxiliar contabilidad', 'contabilidad', 'contador', 'contadora'])) {
            return self::ACCOUNTING;
        }

        if (self::containsAny($candidates, ['nomina', 'nominas'])) {
            return self::PAYROLL;
        }

        if (
            self::containsAny($candidates, ['recursos humanos', 'auxiliar rh', 'auxiliar rrhh'])
            || in_array($role, ['rh', 'rrhh'], true)
        ) {
            return self::HUMAN_RESOURCES;
        }

        if (self::containsAny($candidates, ['operaciones'])) {
            return self::OPERATIONS;
        }

        if (self::containsAny($candidates, ['monitor'])) {
            return self::MONITORING;
        }

        if (self::containsAny($candidates, ['juridico'])) {
            return self::LEGAL;
        }

        if (self::containsAny($candidates, ['supervis'])) {
            return self::SUPERVISOR;
        }

        if (self::containsAny($candidates, ['custodio', 'custodios'])) {
            return self::CUSTODIAN;
        }

        if (self::containsAny($candidates, ['auxiliar administrativo', 'aux admin', 'administrativo', 'imss'])) {
            return self::IMSS;
        }

        return self::USER;
    }

    public static function isAdministrator(User $user): bool
    {
        return self::for($user) === self::ADMIN;
    }

    private static function normalize(mixed $value): string
    {
        return Str::of((string) $value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->toString();
    }

    /** @param list<string> $values */
    private static function containsAny(array $values, array $needles): bool
    {
        foreach ($values as $value) {
            if (Str::contains($value, $needles)) {
                return true;
            }
        }

        return false;
    }
}
