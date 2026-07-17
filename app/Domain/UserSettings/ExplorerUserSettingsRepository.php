<?php declare(strict_types=1);
namespace App\Domain\UserSettings;

use App\Core\Database\ExplorerRepository;

class ExplorerUserSettingsRepository extends ExplorerRepository
{
    public const string COLUMN_ID = 'id';
    public const string COLUMN_USER_ID = 'user_id';
    public const string COLUMN_THEME = 'theme';

    public const string TABLE_NAME = 'user_settings';


    public function __construct(
        private readonly ExplorerUserSettingsMapper $mapper,
    ) {
        parent::__construct(self::TABLE_NAME);
    }


    public function findByUserId(int $userId): ?UserSettings
    {
        $row = $this->getTable()
            ->where(self::COLUMN_USER_ID, $userId)
            ->fetch();

        if ($row === null) {
            return null;
        }

        return $this->mapper->mapUserSettings($row);
    }


    public function upsertForUserId(int $userId, AppearanceTheme $theme): void
    {
        $existing = $this->getTable()
            ->where(self::COLUMN_USER_ID, $userId)
            ->fetch();

        if ($existing !== null) {
            $this->getTable()
                ->where(self::COLUMN_USER_ID, $userId)
                ->update([
                    self::COLUMN_THEME => $theme->value,
                ]);
        } else {
            $this->getTable()
                ->insert([
                    self::COLUMN_USER_ID => $userId,
                    self::COLUMN_THEME   => $theme->value,
                ]);
        }
    }
}
