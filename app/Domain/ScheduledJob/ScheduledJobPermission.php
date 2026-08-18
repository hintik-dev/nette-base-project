<?php declare(strict_types=1);
namespace App\Domain\ScheduledJob;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\TPermissionDefinition;

enum ScheduledJobPermission: string implements PermissionDefinition
{
    use TPermissionDefinition;

    case ListAll = 'scheduled-job.list';
    case Edit = 'scheduled-job.edit';
    case RunNow = 'scheduled-job.run-now';
    case RunHistory = 'scheduled-job.run-history';


    public function getLabel(): string
    {
        return match ($this) {
            self::ListAll    => 'Zobrazit definice úloh',
            self::Edit       => 'Upravit definici úlohy',
            self::RunNow     => 'Spustit úlohu ručně',
            self::RunHistory => 'Zobrazit historii běhů',
        };
    }


    public function getGroup(): string
    {
        return 'Plánované úlohy';
    }
}
