<?php declare(strict_types=1);
namespace App\Domain\UserSettings;

readonly class UserSettings
{
    public function __construct(
        public int $id,
        public int $userId,
        public AppearanceTheme $theme,
    ) {
    }
}
