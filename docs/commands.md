# CLI Příkazy

Projekt používá **Symfony Console** pro CLI příkazy. Příkazy se spouštějí přes `bin/console`.

---

## Spuštění příkazů

Příkazy se spouštějí uvnitř PHP kontejneru:

```bash
# Vstup do PHP kontejneru
make bash

# Spuštění příkazu
php bin/console app:hello-world

# Výpis všech dostupných příkazů
php bin/console list
```

---

## Struktura příkazu

Každý příkaz musí:

1. Ležet v adresáři `app/Command/`
2. Dědit od `App\Command\BaseCommand`
3. Mít PHP atribut `#[AsCommand]` s názvem a popisem

### Vzorový příkaz

```php
<?php declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:muj-prikaz', description: 'Popis příkazu')]
class MujPrikazCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Příkaz proběhl úspěšně!');
        return self::SUCCESS;
    }
}
```

---

## Příkaz se závislostmi

Závislosti se vkládají přes konstruktor (dependency injection):

```php
#[AsCommand(name: 'app:sync-users', description: 'Synchronizace uživatelů')]
class SyncUsersCommand extends BaseCommand
{
    public function __construct(
        private readonly UserService $userService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->userService->getAllUsers();
        $output->writeln(sprintf('Synchronizováno %d uživatelů', count($users)));
        return self::SUCCESS;
    }
}
```

---

## Příkaz s argumenty a options

```php
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'app:send-email', description: 'Odeslání emailu')]
class SendEmailCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Cílová emailová adresa')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Odeslat i bez potvrzení');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $force = $input->getOption('force');

        $output->writeln("Odesílám email na: $email");
        return self::SUCCESS;
    }
}
```

Spuštění:
```bash
php bin/console app:send-email user@example.com --force
```

---

## Registrace příkazů

Příkazy jsou **automaticky registrovány** díky konfiguraci v `config/services.neon`:

```neon
search:
    - in: %appDir%/Command
      classes:
          - *Command
```

Stačí tedy vytvořit třídu v `app/Command/` — není potřeba žádná manuální registrace.

---

## Návratové kódy

| Konstanta        | Hodnota | Význam                |
|------------------|---------|-----------------------|
| `self::SUCCESS`  | 0       | Příkaz proběhl OK     |
| `self::FAILURE`  | 1       | Příkaz selhal         |
| `self::INVALID`  | 2       | Neplatné použití      |

---

## Ukázkový příkaz v projektu

Projekt obsahuje demonstrační příkaz `HelloWorldCommand`:

```bash
php bin/console app:hello-world
# Výstup: Hello World!
```

Zdrojový kód: `app/Command/HelloWorldCommand.php`
