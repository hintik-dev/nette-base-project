<?php declare(strict_types=1);
namespace App\Domain\UserSettings;

use App\Model\Security\SecurityUser;

class UserSettingsFacade
{
    public function __construct(
        private readonly UserSettingsService $userSettingsService,
        private readonly SecurityUser $securityUser,
    ) {
    }


    public function getCurrentUserSettings(): UserSettings
    {
        return $this->userSettingsService->getUserSettings(
            (int) $this->securityUser->getId(),
        );
    }


    public function saveCurrentUserTheme(AppearanceTheme $theme): void
    {
        $this->userSettingsService->saveUserTheme(
            (int) $this->securityUser->getId(),
            $theme,
        );
    }
}
