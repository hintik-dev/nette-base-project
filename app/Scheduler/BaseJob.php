<?php declare(strict_types=1);

namespace App\Scheduler;

/**
 * Základní třída pro všechny plánované úlohy.
 * Rozšiřte tuto třídu a implementujte metodu run(OutputInterface $output).
 */
abstract class BaseJob implements IScheduledJob
{
}
