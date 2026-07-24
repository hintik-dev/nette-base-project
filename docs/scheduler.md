# Job systém (plánované úlohy)

Projekt obsahuje DB-driven job systém postavený na [contributte/scheduler](https://github.com/contributte/scheduler). Definice úloh (název, třída, cron výraz, aktivní/neaktivní) i historie jejich běhů se spravují v databázi přes `Admin:ScheduledJob` — žádná úloha se neregistruje v konfiguraci.

---

## Jak to funguje

```
bin/console scheduler:run   (spouštěno cronem každou minutu)
        │
        ▼
DatabaseScheduler::run()
        │
        ├─ plan()     … pro každou aktivní úlohu s "due" cron výrazem vytvoří
        │                záznam scheduled_job_run (stav "scheduled")
        │
        └─ execute()  … zpracuje všechny čekající záznamy (scheduled → running
                         → completed/failed), volá IScheduledJob::run()
```

Tlačítko **„Spustit hned“** v `Admin:ScheduledJob` dělá totéž co `plan()` ručně (`ScheduledJobRunTrigger::Manual`) — záznam se vytvoří okamžitě, ale zpracuje ho až následující běh `scheduler:run`.

---

## Vytvoření nové úlohy

1. Vytvořte třídu v `app/Scheduler/`, rozšiřte `App\Scheduler\BaseJob` a implementujte `run()`:

```php
<?php declare(strict_types=1);

namespace App\Scheduler;

use Symfony\Component\Console\Output\OutputInterface;

class SyncDataJob extends BaseJob
{
    public function __construct(
        private readonly DataSyncService $dataSyncService,
    ) {
    }

    public function run(OutputInterface $output): void
    {
        $output->writeln('Synchronizace spuštěna...');
        $count = $this->dataSyncService->sync();
        $output->writeln("Synchronizováno $count záznamů.");
    }
}
```

   Třídy v `app/Scheduler/` odpovídající vzoru `*Job` jsou **automaticky registrovány** v DI kontejneru (`search` v `config/services.neon`) — není potřeba žádná ruční registrace.

   `$output` je `App\Scheduler\DatabaseOutput` — vše, co do něj napíšete přes `writeln()`, se po doběhnutí uloží do výpisu běhu (`scheduled_job_run_output`) a je vidět v detailu běhu v Adminu.

2. Zaregistrujte úlohu v databázi — buď migrací (viz `db/migrations/20260717080000_create_scheduled_job_tables.php` pro příklad seed dat), nebo ručně přes `Admin:ScheduledJob`:

```php
$this->table('scheduled_job')->insert([
    'name'        => 'Synchronizace dat',
    'class'       => 'App\\Scheduler\\SyncDataJob',
    'description' => 'Pravidelná synchronizace externích dat.',
    'cron'        => '*/15 * * * *',
    'is_active'   => true,
    'created_at'  => date('Y-m-d H:i:s'),
]);
```

   Sloupec `class` musí obsahovat plně kvalifikovaný název třídy přesně tak, jak je zaregistrovaná v DI kontejneru.

---

## Logování průběhu

Do výpisu běhu se automaticky zapisují checkpointy životního cyklu (zaregistrování, naplánování, spuštění, dokončení/chyba) — vlastní úloha do stejného výpisu přidává jen svoje vlastní řádky přes `$output->writeln(...)`. Text výjimky se při chybě zapíše do výpisu, **ne** do entity běhu — `ScheduledJobRun` nese pouze výsledný stav (`Scheduled`/`Running`/`Completed`/`Failed`), detail chyby je vždy až v `scheduled_job_run_output`.

---

## Vlastní databázové spojení

Repozitáře job systému (`ExplorerScheduledJob*Repository`) dědí od `App\Core\Database\SchedulerExplorerRepository`, ne od běžné `ExplorerRepository`. Používají tak **samostatné databázové spojení** (`database.scheduler`, konfigurované v `config/local/database.neon` vedle `database.default`), nezávislé na hlavním spojení aplikace. Díky tomu jde zalogovat chybu běhu úlohy i v situaci, kdy je hlavní spojení v nekonzistentním stavu (např. rozpadlá transakce po chybě, kterou úloha sama způsobila).

Pokud vytváříte repozitář, který by měl logicky patřit k job systému (např. pomocná tabulka používaná výhradně úlohami), zvažte zdědění od `SchedulerExplorerRepository` místo `ExplorerRepository` ze stejného důvodu.

---

## Cron

`bin/console scheduler:run` musí být spouštěn pravidelně (typicky každou minutu) systémovým cronem — appka sama o sobě žádný interní scheduler neběží. V `docker/` prostředí je potřeba cron nastavit ručně (např. cronjob na hostitelském systému volající `docker compose exec php-apache php bin/console scheduler:run`, nebo cron přímo uvnitř kontejneru).

Další užitečné příkazy z `contributte/scheduler` (dostupné přes `bin/console`, stejně jako [vlastní CLI příkazy](commands.md)):

```bash
php bin/console scheduler:list         # výpis všech úloh a jejich cron výrazů
php bin/console scheduler:force-run    # okamžité spuštění bez ohledu na cron výraz
```

---

## Admin rozhraní

| Stránka | Popis |
|---|---|
| `Admin:ScheduledJob:default` | Definice úloh — přehled, aktivace/deaktivace, „Spustit hned“ |
| `Admin:ScheduledJob:runs` | Historie běhů (všech úloh, nebo filtrovaná na jednu přes `jobId`) |
| `Admin:ScheduledJob:runDetail` | Detail běhu — stav, trvání, kompletní výpis |
