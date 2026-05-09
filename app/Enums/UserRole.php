<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Couple = 'couple';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Couple => 'Couple',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    public function isCouple(): bool
    {
        return $this === self::Couple;
    }
}
