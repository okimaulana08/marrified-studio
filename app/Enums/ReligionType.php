<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Religion of the couple — drives which religious_text JSON keys are expected
 * (e.g. islam → ayat/translation/source; christian → bible_verse/translation;
 * none → no religious text section rendered).
 */
enum ReligionType: string
{
    case Islam = 'islam';
    case Christian = 'christian';
    case Catholic = 'catholic';
    case Hindu = 'hindu';
    case Buddhist = 'buddhist';
    case None = 'none';

    public function label(): string
    {
        return match ($this) {
            self::Islam => 'Islam',
            self::Christian => 'Kristen',
            self::Catholic => 'Katolik',
            self::Hindu => 'Hindu',
            self::Buddhist => 'Buddha',
            self::None => 'Tanpa teks religius',
        };
    }

    /**
     * Field keys expected inside Invitation::$religious_text for this religion.
     *
     * @return list<string>
     */
    public function fieldKeys(): array
    {
        return match ($this) {
            self::Islam => ['ayat', 'translation', 'source'],
            self::Christian, self::Catholic => ['verse', 'translation', 'source'],
            self::Hindu => ['mantra', 'translation', 'source'],
            self::Buddhist => ['sutra', 'translation', 'source'],
            self::None => [],
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}
