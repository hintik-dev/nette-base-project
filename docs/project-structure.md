# Struktura projektu

## Adresářová struktura

```
nette-base-project/
├── app/                    # Zdrojový kód aplikace
├── assets/                 # Frontend zdrojové soubory (CSS, JS, obrázky)
├── bin/                    # Spustitelné soubory (CLI konzole)
├── config/                 # Konfigurační soubory (formát NEON)
├── db/                     # Databázové migrace a inicializační SQL
├── docker/                 # Docker konfigurace (Compose, env soubory)
├── docs/                   # Tato dokumentace
├── log/                    # Logy aplikace (zapisovatelná složka)
├── temp/                   # Cache a dočasné soubory (zapisovatelná složka)
├── tests/                  # Testy aplikace
├── vendor/                 # Závislosti Composer (nespravovat ručně)
├── www/                    # Webový root (veřejně přístupná složka)
├── Makefile                # Make příkazy pro vývoj
├── composer.json           # PHP závislosti a skripty
├── package.json            # Node.js závislosti
├── phinx.php               # Konfigurace migrací
├── phpstan.neon            # Konfigurace statické analýzy
├── .phpcs.xml              # Konfigurace coding standardu
├── rector.php              # Konfigurace Rector (refaktoring)
└── vite.config.ts          # Konfigurace Vite (build assetů)
```

---

## Docker kontejnery

Projekt běží v Docker prostředí se čtyřmi kontejnery:

### `php-apache` — Webový server

- **Obraz:** `thecodingmachine/php:8.5-v5-apache`
- **Port:** výchozí `10000`
- **Popis:** Hlavní kontejner s PHP 8.5 a Apache. Hostuje webovou aplikaci, spouští CLI příkazy a nástroje code quality.
- Při startu automaticky spustí `composer install`.

### `database` — Databáze

- **Obraz:** `mariadb:latest`
- **Port:** výchozí `20000`
- **Popis:** MariaDB databázový server. Při prvním spuštění se inicializuje souborem `db/database_init_state.sql`.

### `phpmyadmin` — Správa databáze

- **Obraz:** phpMyAdmin
- **Port:** výchozí `30000`
- **Popis:** Webové rozhraní pro správu databáze. Dostupné na `http://localhost:30000`.

### `nodejs` — Frontend build

- **Obraz:** Node.js 24
- **Popis:** Kontejner pro buildování frontend assetů (CSS, JS) pomocí Vite. Běží v idle módu a čeká na npm příkazy.

---

## Architektura aplikace

Projekt implementuje **třívrstvou MVP architekturu** (Model–View–Presenter):

```
┌─────────────────────────────────────────────────────┐
│                  PRESENTATION LAYER                 │
│         Presentery, Komponenty, Šablony             │
└────────────────────┬────────────────────────────────┘
                     │ volá
┌────────────────────▼────────────────────────────────┐
│                   DOMAIN LAYER                      │
│         Fasády, Služby, Entity, Výjimky             │
└────────────────────┬────────────────────────────────┘
                     │ volá
┌────────────────────▼────────────────────────────────┐
│               INFRASTRUCTURE LAYER                  │
│         Repozitáře, Mappery, Databáze               │
└─────────────────────────────────────────────────────┘
```

---

## Adresářová struktura app/

```
app/
├── Bootstrap.php                        # Inicializace aplikace (DI kontejner)
├── Command/                             # CLI příkazy (Symfony Console)
│   ├── BaseCommand.php                  # Abstraktní základ pro příkazy
│   ├── HelloWorldCommand.php            # Ukázkový příkaz
│   └── CreateSuperAdminCommand.php      # Vytvoření/aktualizace superadmina
├── Core/                                # Technické jádro frameworku
│   ├── RouterFactory.php                # Definice URL routování
│   └── Database/
│       ├── ExplorerRepository.php       # Základní třída pro repozitáře (database.default)
│       └── SchedulerExplorerRepository.php  # Repozitáře job systému (database.scheduler)
├── Scheduler/                           # Job systém — viz docs/scheduler.md
│   ├── IScheduledJob.php                # Interface pro plánované úlohy
│   ├── BaseJob.php                      # Abstraktní základ pro úlohy
│   ├── HelloWorldJob.php                # Ukázková úloha (výchozí neaktivní)
│   ├── DatabaseScheduler.php            # DB-driven implementace IScheduler
│   ├── DbJobWrapper.php                 # Adaptér pro contributte/scheduler CLI
│   └── DatabaseOutput.php               # Zachytává výstup běhu do DB
├── Domain/                              # Doménová business logika
│   ├── User/                            # Doména uživatelů
│   │   ├── User.php                     # Entita uživatele
│   │   ├── UserFacade.php               # Fasáda (bezpečnostní kontroly)
│   │   ├── UserService.php              # Business logika
│   │   ├── ExplorerUserRepository.php   # Přístup k datům
│   │   ├── ExplorerUserMapper.php       # Mapování DB řádků na entity
│   │   └── UserNotFoundException.php    # Doménová výjimka
│   ├── UserRole/
│   │   └── UserRole.php                 # Enum rolí (superadmin/admin/user)
│   ├── ScheduledJob/                    # Doména job systému — viz docs/scheduler.md
│   │   ├── ScheduledJob.php             # Entita definice úlohy
│   │   ├── ScheduledJobRun.php          # Entita běhu úlohy
│   │   ├── ScheduledJobRunOutput.php    # Entita řádku výpisu
│   │   ├── ScheduledJobRunStatus.php    # Enum stavů běhu
│   │   ├── ScheduledJobRunTrigger.php   # Enum spouštěčů (scheduler/manual)
│   │   ├── ScheduledJobFacade.php
│   │   ├── ScheduledJobService.php
│   │   └── Explorer*Repository.php      # Repozitáře (dědí SchedulerExplorerRepository)
│   └── Sign/                            # Doména přihlašování
│       ├── SignFacade.php
│       ├── SignService.php
│       └── SignInFormData.php           # DTO pro přihlašovací formulář
├── Model/                               # Technické modely a utility
│   ├── Security/                        # Autentizace a autorizace
│   │   ├── Identity.php
│   │   ├── SecurityUser.php
│   │   ├── Passwords.php
│   │   ├── Authenticator/
│   │   │   └── UserAuthenticator.php
│   │   └── Authorizator/
│   │       └── StaticAuthorizator.php   # Role-based access control
│   ├── Latte/                           # Rozšíření šablonovacího engine
│   │   ├── Filters.php
│   │   └── TemplateFactory.php
│   └── Utils/
│       ├── DateTimeFormat.php
│       ├── DateTimeFactory.php          # Konverze timestampů (timezone-aware)
│       └── FlashMessage.php             # Hodnotový objekt pro flash zprávy
├── Lang/                                # Překlady (contributte/translation) — viz docs/translations.md
│   └── contributte_datagrid.cs_CZ.neon
└── Presentation/                        # Prezentační vrstva (MVP)
    ├── Accessory/
    │   └── LatteExtension.php           # Rozšíření pro Latte
    ├── Control/
    │   ├── TFlashMessage.php            # Trait pro flashSuccess/flashError/... (presenter)
    │   ├── TComponentFlashMessage.php   # Totéž pro komponenty (probublává přes onFlash)
    │   ├── Form/
    │   │   └── BaseForm.php             # Základní třída formulářů
    │   └── DataGrid/                    # Vrstva nad ublaboo/datagrid — viz docs/datagrid.md
    │       ├── BaseGrid.php             # Rozšířený Contributte\Datagrid\Datagrid
    │       ├── BaseGridFactory.php      # Factory (autowiring translatoru)
    │       ├── Column/                  # Vlastní sloupce (kopírovací tlačítko, min-width, ...)
    │       └── templates/               # AdminLTE/Bootstrap 5 šablony gridu
    ├── Components/                      # Znovupoužitelné UI komponenty
    │   ├── Base/
    │   │   ├── BaseComponent.php        # Základ pro všechny komponenty
    │   │   └── BaseGridComponent.php    # Základ pro grid komponenty (viz docs/datagrid.md)
    │   └── Admin/
    │       ├── Sign/
    │       │   └── SignInForm/
    │       │       ├── SignInForm.php
    │       │       └── SignInFormFactory.php
    │       ├── User/
    │       │   └── UserListGrid/
    │       └── ScheduledJob/
    │           ├── ScheduledJobGrid/
    │           ├── ScheduledJobRunGrid/
    │           └── ScheduledJobRunOutputGrid/
    └── Modules/                         # Moduly aplikace
        ├── Base/
        │   └── BasePresenter.php        # Základ pro všechny presentery
        ├── Admin/                       # Admin modul (chráněná sekce)
        │   ├── BaseAdminPresenter.php
        │   ├── @layout.latte            # AdminLTE layout (navbar, sidebar, flashes)
        │   ├── Home/
        │   │   └── HomePresenter.php
        │   ├── Sign/
        │   │   └── SignPresenter.php
        │   ├── User/
        │   │   └── UserPresenter.php
        │   └── ScheduledJob/
        │       └── ScheduledJobPresenter.php
        ├── Web/                         # Web modul (veřejná sekce)
        │   ├── BaseWebPresenter.php
        │   └── Home/
        │       └── HomePresenter.php
        └── Error/                       # Chybové stránky
            ├── Error4xx/
            └── Error5xx/
```

---

## Typy souborů a jejich role

### `*Mapper` — Datový mapper

**Umístění:** `app/Domain/{Doména}/Explorer{Entita}Mapper.php`

Zodpovídá za **konverzi databázového záznamu** (`ActiveRow`) na doménovou entitu (PHP objekt). Mapper izoluje ostatní vrstvy od struktury databáze.

```php
class ExplorerUserMapper
{
    public function mapUser(ActiveRow $row): User
    {
        return new User(
            id: $row['id'],
            email: $row['email'],
            // ...
        );
    }
}
```

---

### `*Repository` — Repozitář

**Umístění:** `app/Domain/{Doména}/Explorer{Entita}Repository.php`

Zodpovídá za **přístup k datovému úložišti** (databáze). Obsahuje dotazy a CRUD operace. Vrací doménové entity (přes mapper) nebo primitivní typy. Dědí od `ExplorerRepository`.

```php
class ExplorerUserRepository extends ExplorerRepository
{
    public function getUserById(int $id): User { ... }
    public function updateUserLastLogin(int $id, DateTimeInterface $lastLogin): void { ... }
}
```

Základní třída `ExplorerRepository` poskytuje metody:
- `find(int $id)` — najde záznam podle ID
- `findAll()` — vrátí všechny záznamy
- `findOneBy(array $criteria)` — najde jeden záznam podle kritérií
- `findBy(array $criteria, ...)` — vrátí záznamy podle kritérií
- `getTable()` — vrátí Nette `Selection` pro danou tabulku

Repozitáře standardně používají hlavní databázové spojení (`database.default`), napojené automaticky přes `decorator` v `config/services.neon`. Job systém (viz [Job systém](scheduler.md)) používá druhé, nezávislé spojení (`database.scheduler`) přes `App\Core\Database\SchedulerExplorerRepository` — vlastní repozitář dědí od ní místo od `ExplorerRepository`, pokud logicky patří k job systému a mělo by být na hlavním spojení nezávislé.

---

### `*Service` — Služba

**Umístění:** `app/Domain/{Doména}/{Doména}Service.php`

Obsahuje **čistou business logiku** bez bezpečnostních kontrol. Volá repozitáře a pracuje s doménovými entitami. Neví nic o přihlášeném uživateli ani o HTTP vrstvě.

```php
class UserService
{
    public function getUserById(int $id): User
    {
        return $this->userRepository->getUserById($id);
    }
}
```

---

### `*Facade` — Fasáda

**Umístění:** `app/Domain/{Doména}/{Doména}Facade.php`

**Vstupní bod do doménové logiky** pro presentery a komponenty. Fasáda provádí bezpečnostní kontroly (oprávnění, přihlášení) a pak volá `*Service`. Prezentační vrstva by měla vždy volat fasádu, nikdy přímo service.

```php
class UserFacade
{
    public function getUserById(int $id): User
    {
        if (!$this->securityUser->isAllowed('user', 'detail')) {
            throw new InsufficientPrivilegesException();
        }
        return $this->userService->getUserById($id);
    }
}
```

---

### `*Presenter` — Presenter

**Umístění:** `app/Presentation/Modules/{Modul}/{Sekce}/{Název}Presenter.php`

Zpracovává **HTTP požadavky** a řídí tok aplikace. Volá fasády, připravuje data pro šablony a přesměrovává. Nette automaticky mapuje URL na presenter a action metody.

Konvence pojmenování action metod:
- `actionDefault()` — výchozí akce
- `actionDetail(int $id)` — detail záznamu
- `renderDefault()` — příprava dat pro šablonu

---

### Rozdělení Admin a Web modulu

| Modul   | Třída          | URL prefix | Přístup        |
|---------|----------------|------------|----------------|
| `Admin` | `BaseAdminPresenter` | `/admin/…` | Pouze přihlášení uživatelé s rolí admin |
| `Web`   | `BaseWebPresenter`   | `/…`       | Veřejně přístupné |
| `Error` | —              | —          | Chybové stránky (4xx, 5xx) |

---

## Automatická registrace služeb

Třídy odpovídající vzorům jsou **automaticky registrovány** v DI kontejneru (viz `config/services.neon`):

```neon
search:
    - in: %appDir%
      classes:
          - *Facade
          - *Factory
          - *Repository
          - *Service
          - *Mapper
    - in: %appDir%/Command
      classes:
          - *Command
    - in: %appDir%/Scheduler
      classes:
          - *Job
```

Díky tomu není nutné ručně registrovat každou třídu — stačí dodržovat konvence pojmenování. Třídy plánovaných úloh (`*Job` v `app/Scheduler/`) jsou tak dostupné přes `Container::findByType(App\Scheduler\IScheduledJob::class)`, což využívá job systém k dohledání implementace podle názvu třídy uložené v databázi — viz [Job systém](scheduler.md).

Pokud třída nesplňuje žádný z výše uvedených vzorů, ale její registrace v DI kontejneru je smysluplná a odůvodněná, lze ji zaregistrovat ručně v `config/services.neon`. Typickým příkladem je pomocná třída v doménové vrstvě, jejíž název nepasuje na žádný ze vzorů — například kalkulátor nebo konvertor:

```neon
services:
    - App\Domain\Order\OrderPriceCalculator
```
