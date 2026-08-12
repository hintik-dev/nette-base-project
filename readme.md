# Nette Base Project

Skeleton Nette aplikace připravený pro okamžitý vývoj. Obsahuje předkonfigurované prostředí, Docker setup, třívrstvou MVP architekturu a sadu nástrojů pro zajištění kvality kódu.

---

## Funkce

- **Docker prostředí** — PHP 8.5 + Apache, MariaDB, phpMyAdmin, Node.js
- **Třívrstvá MVP architektura** — oddělení prezentační, doménové a datové vrstvy
- **Modulární struktura** — samostatný Admin a Web modul
- **Admin design systém** — AdminLTE 4 + Bootstrap 5 (layout, sidebar, přihlášení)
- **DataGrid** — vlastní vrstva nad ublaboo/datagrid s jednotným vzhledem a znovupoužitelnou `BaseGridComponent`
- **Job systém** — DB-driven plánované úlohy (contributte/scheduler) s historií běhů a detailním logováním
- **Jazykové mutace** — contributte/translation pro texty generované knihovnami (např. DataGrid)
- **Autentizace a autorizace** — přihlašování uživatelů, role-based access control
- **Flash zprávy** — typované `flashSuccess`/`flashError`/`flashWarning`/`flashInfo` v presenterech i komponentách
- **Databázové migrace** — Phinx pro verzování databázové struktury
- **CLI příkazy** — Symfony Console pro konzolové operace
- **Frontend build** — Vite pro moderní správu CSS a JS assetů
- **Formuláře jako komponenty** — znovupoužitelné UI komponenty s BaseComponent
- **Automatická registrace služeb** — Facade, Service, Repository, Mapper, Factory, Command, Job
- **Kompletní code quality toolchain** — PHPStan, PHPCS, Rector, Latte lint, Neon lint
- **Testování** — Nette Tester připraven k použití

---

## Požadavky

- Docker a Docker Compose
- Make

---

## Rychlý start

```bash
# Klonování projektu
git clone https://github.com/hintik-dev/nette-base-project.git
cd nette-base-project

# Konfigurace prostředí
cp docker/.env_example docker/.env
cp docker/php.env_example docker/php.env
cp config/local.example/ config/local -r

# Vyplníme nastavení připojení do databáze v config/local/database.neon (při použití jiné než vývojové databáze v kontejneru).

# Spuštění
make up

# Nastavení práv (zapisovatelné složky)
make chmod

# Databázové migrace
make migrate

# Instalace NPM závislostí
make ni

# Build assetů
make node-build-dev
```

Aplikace bude dostupná na `http://localhost:10000` (port určen dle obsahu souboru `docker/.env`).

Podrobný návod: [docs/getting-started.md](docs/getting-started.md)

---

## Dokumentace

| Dokument | Popis |
|---|---|
| [Spuštění projektu](docs/getting-started.md) | Konfigurace prostředí, Docker, první spuštění |
| [Struktura projektu](docs/project-structure.md) | Architektura, vrstvy, typy souborů, kontejnery |
| [Komponenty](docs/components.md) | Tvorba komponent, factory, registrace, flash zprávy |
| [Formuláře](docs/forms.md) | Formuláře v komponentách, validace, DTO |
| [DataGrid](docs/datagrid.md) | Tvorba grid komponent, sloupce, filtry, akce |
| [ACL](docs/acl.md) | Role a oprávnění, priority, vynucování přístupu |
| [Job systém](docs/scheduler.md) | Plánované úlohy, logování běhu, cron |
| [Jazykové mutace](docs/translations.md) | contributte/translation, překladové soubory |
| [CLI Příkazy](docs/commands.md) | Psaní a spouštění konzolových příkazů |
| [Migrace](docs/migrations.md) | Správa databázové struktury přes Phinx |
| [Testování](docs/testing.md) | Psaní a spouštění testů (Nette Tester) |
| [Vývojový workflow](docs/workflow.md) | Konvence, nástroje kvality, Make příkazy |

---

## Architektura

Projekt implementuje třívrstvou MVP architekturu:

```
Presentation  →  Domain  →  Infrastructure
(Presentery)     (Fasády,    (Repozitáře,
                 Služby,      Mappery,
                 Entity)      Databáze)
```

Aplikace je rozdělena do dvou modulů:
- **Admin** (`/admin/…`) — přihlášení správci
- **Web** (`/…`) — veřejná část webu

---

## Make příkazy

```bash
make up             # Spuštění Docker prostředí
make bash           # Bash v PHP kontejneru
make migrate        # Spuštění databázových migrací
make watch          # Watch mód pro assety
make all            # Všechny testy a kontroly kódu
make all-fix        # Automatická oprava všech problémů
make nette-tester   # Spuštění testů
make phpstan        # Statická analýza
```

Kompletní přehled příkazů: [docs/workflow.md](docs/workflow.md)

---

## Technologie

| Oblast | Technologie |
|---|---|
| Framework | [Nette 3.2](https://nette.org) |
| PHP | 8.5+ |
| Databáze | MariaDB (Nette Database Explorer) |
| Migrace | [Phinx 0.16](https://phinx.org) |
| CLI | [Symfony Console 7](https://symfony.com/doc/current/console.html) |
| Job systém | [contributte/scheduler](https://github.com/contributte/scheduler) |
| DataGrid | [ublaboo/datagrid (contributte)](https://github.com/contributte/datagrid) |
| Jazykové mutace | [contributte/translation](https://github.com/contributte/translation) |
| Admin design | [AdminLTE 4](https://adminlte.io) + [Bootstrap 5](https://getbootstrap.com) |
| Frontend | [Vite](https://vitejs.dev) |
| Statická analýza | [PHPStan 2](https://phpstan.org) |
| Coding standard | [PHP_CodeSniffer 3](https://github.com/PHPCSStandards/PHP_CodeSniffer) |
| Refaktoring | [Rector 2](https://getrector.com) |
| Testování | [Nette Tester 2.6](https://tester.nette.org) |

---

## Autor

**Jan Hinterholzinger** — [jan@hinterholzinger.cz](mailto:jan@hinterholzinger.cz)

GitHub: [hintik-dev](https://github.com/hintik-dev)

---

Licencováno pod [MIT licencí](LICENSE).
