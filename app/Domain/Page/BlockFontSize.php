<?php declare(strict_types=1);
namespace App\Domain\Page;

/**
 * Zrcadlí assets/admin/editor/fontSize.ts (FONT_SIZE_PX) — sdílené mezi
 * bloky Text, NumberedList a BulletList.
 */
enum BlockFontSize: string
{
    case Small = 'small';
    case Normal = 'normal';
    case Large = 'large';
    case XLarge = 'xlarge';

    public function toRem(): string
    {
        return match ($this) {
            self::Small => '0.875rem',
            self::Normal => '1rem',
            self::Large => '1.25rem',
            self::XLarge => '1.5rem',
        };
    }


    public static function fromProps(mixed $value): self
    {
        return self::tryFrom((string) $value) ?? self::Normal;
    }
}
