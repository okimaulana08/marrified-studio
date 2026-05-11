<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Categorization for guests. Used for filtering in the Tamu tab + group
 * breakdown in Analytics. Optional — guests without a group fall under
 * "Lainnya" in displays.
 */
enum GuestGroup: string
{
    case Family = 'family';
    case Friend = 'friend';
    case Coworker = 'coworker';
    case Vendor = 'vendor';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Family => 'Keluarga',
            self::Friend => 'Sahabat',
            self::Coworker => 'Rekan Kerja',
            self::Vendor => 'Vendor',
            self::Other => 'Lainnya',
        };
    }

    /** @return array<int, array{value: string, label: string}> */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $c) {
            $out[] = ['value' => $c->value, 'label' => $c->label()];
        }

        return $out;
    }

    public static function labelFor(?string $value): string
    {
        if ($value === null || $value === '') {
            return 'Lainnya';
        }
        $case = self::tryFrom($value);

        return $case?->label() ?? 'Lainnya';
    }
}
