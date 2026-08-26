<?php

namespace App\Enums;

enum RequestStatus: string
{
    case IN_PROGRESS = 'En Proceso';
    case ACCEPTED = 'Aceptada';
    case REJECTED = 'Rechazada';
    case CANCELLED = 'Cancelada';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::IN_PROGRESS => in_array($target, [self::ACCEPTED, self::REJECTED, self::CANCELLED], true),
            self::ACCEPTED, self::REJECTED, self::CANCELLED => false,
        };
    }

    public static function transition(string $current, self $target): self
    {
        $source = self::tryFrom($current);

        if (! $source || ! $source->canTransitionTo($target)) {
            throw new \DomainException("No se permite cambiar el estado de '$current' a '{$target->value}'.");
        }

        return $target;
    }
}
