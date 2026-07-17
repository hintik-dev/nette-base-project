<?php declare(strict_types=1);

namespace App\Core\Database;

use Nette\Database\Explorer;

/**
 * Základní třída pro repozitáře job systému.
 * Používá vlastní databázové spojení (database.scheduler), nezávislé na hlavním
 * spojení, aby šlo zalogovat chybu běhu úlohy i pokud je hlavní spojení v
 * nekonzistentním stavu (např. rozpadlá transakce).
 */
abstract class SchedulerExplorerRepository extends ExplorerRepository
{
    public function injectSchedulerDependencies(Explorer $explorer): void
    {
        $this->database = $explorer;
    }
}
