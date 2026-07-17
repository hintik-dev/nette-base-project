<?php declare(strict_types=1);

namespace App\Scheduler;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface, který musí implementovat každá plánovaná úloha.
 *
 * Příklad nové úlohy:
 *
 *   class MojeUloha extends BaseJob
 *   {
 *       public function __construct(private readonly MojaService $service) {}
 *
 *       public function run(OutputInterface $output): void
 *       {
 *           $output->writeln('Spouštím úlohu...');
 *           $this->service->neco();
 *           $output->writeln('Hotovo.');
 *       }
 *   }
 *
 * Po vytvoření třídy ji zaregistruj v databázi (tabulka scheduled_job):
 *   - class: App\Scheduler\MojeUloha
 *   - cron:  nastavení spouštění (např. 0 * * * *)
 *   - is_active: true
 */
interface IScheduledJob
{
    public function run(OutputInterface $output): void;
}
