<?php declare(strict_types=1);
namespace App\Domain\UserSettings;

readonly class UserSettingsService
{
    public function __construct(
        private ExplorerUserSettingsRepository $userSettingsRepository,
    ) {
    }


    public function getUserSettings(int $userId): UserSettings
    {
        return $this->userSettingsRepository->findByUserId($userId)
            ?? new UserSettings(id: 0, userId: $userId, theme: AppearanceTheme::Light);
    }


    public function saveUserTheme(int $userId, AppearanceTheme $theme): void
    {
        $this->userSettingsRepository->upsertForUserId($userId, $theme);
    }
}
