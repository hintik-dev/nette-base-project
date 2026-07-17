<?php declare(strict_types=1);
namespace App\Domain\UserSettings;

use Nette\Database\Table\ActiveRow;

class ExplorerUserSettingsMapper
{
    public function mapUserSettings(ActiveRow $row): UserSettings
    {
        return new UserSettings(
            id:     $row[ExplorerUserSettingsRepository::COLUMN_ID],
            userId: $row[ExplorerUserSettingsRepository::COLUMN_USER_ID],
            theme:  AppearanceTheme::from($row[ExplorerUserSettingsRepository::COLUMN_THEME]),
        );
    }
}
