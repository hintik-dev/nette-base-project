# Vývojový workflow

## Konvence pojmenování

### PHP třídy

| Typ třídy       | Konvence                       | Příklad                          |
|-----------------|--------------------------------|----------------------------------|
| Entita          | PascalCase                     | `User`, `Article`                |
| Repozitář       | `Explorer{Entita}Repository`   | `ExplorerUserRepository`         |
| Mapper          | `Explorer{Entita}Mapper`       | `ExplorerUserMapper`             |
| Služba          | `{Doména}Service`              | `UserService`                    |
| Fasáda          | `{Doména}Facade`               | `UserFacade`                     |
| Presenter       | `{Název}Presenter`             | `HomePresenter`, `SignPresenter` |
| Komponenta      | `{Název}` (popisná)            | `SignInForm`, `UserList`         |
| Factory         | `{Komponenta}Factory`          | `SignInFormFactory`              |
| Příkaz          | `{Akce}Command`                | `HelloWorldCommand`              |
| Výjimka         | `{Popis}Exception`             | `UserNotFoundException`          |
| DTO             | `{Formulář}Data`               | `SignInFormData`                 |

### Soubory a adresáře

- Adresáře: PascalCase (`SignInForm/`, `Admin/`)
- PHP soubory: odpovídají názvu třídy (`UserService.php`)
- Latte šablony: shodný název jako komponenta/presenter (`SignInForm.latte`)
- Neon konfigurace: snake_case nebo camelCase (`common.neon`, `database.neon`)

### Metody presenterů

- `actionDefault()` — výchozí akce presenteru
- `action{Akce}()` — pojmenovaná akce (zpracování vstupu, přesměrování)
- `render{Akce}()` — příprava dat pro šablonu
- `createComponent{Komponenta}()` — registrace komponenty

---

## Nástroje pro kontrolu kvality kódu

### make all — Komplexní kontrola

Spustí všechny kontroly najednou:

```bash
make all
```

Ekvivalent `composer run all`, který spouští:
- PHPStan (statická analýza)
- PHPCS (coding standard)
- Latte lint (šablony)
- Neon lint (konfigurace)
- Rector (dry-run)
- Nette Tester (testy)

### make all-fix — Automatická oprava

Automaticky opraví vše co lze opravit:

```bash
make all-fix
```

---

## Jednotlivé nástroje

### PHPStan — Statická analýza

PHPStan analyzuje kód a hledá chyby bez spuštění aplikace (chybějící typy, neexistující metody, atd.).

```bash
make phpstan
# nebo zkratka:
make ps
```

Konfigurace: `phpstan.neon`

Úroveň analýzy je nastavena na maximum. PHPStan kontroluje:
- Typové bezpečí
- Neexistující metody a třídy
- Chybějící return typy
- Nepoužité proměnné

---

### PHPCS / PHPCBF — Coding standard

PHPCS kontroluje, zda kód dodržuje definovaný coding standard (PSR-12 a rozšíření).

```bash
# Zobrazení chyb
make phpcs

# Automatická oprava
make phpcs-fix
# nebo zkratka:
make pf
```

Konfigurace: `.phpcs.xml`

---

### Rector — Automatický refaktoring

Rector navrhuje modernizaci kódu (unused imports, deprecated API, atd.).

```bash
# Zobrazení navrhovaných změn (dry-run)
make rector

# Aplikace změn
make rector-fix
# nebo zkratka:
make rf
```

Konfigurace: `rector.php`

---

### Latte lint — Validace šablon

Kontroluje syntaxi Latte šablon:

```bash
make latte-lint
# nebo zkratka:
make ll
```

---

### Neon lint — Validace konfigurace

Kontroluje syntaxi NEON konfiguračních souborů:

```bash
make neon-lint
# nebo zkratka:
make nl
```

---

### Strict types — Striktní typy

Kontroluje, zda mají všechny PHP soubory deklaraci `declare(strict_types=1)`:

```bash
# Zobrazení souborů bez deklarace (dry-run)
make strict-types

# Přidání deklarace do všech souborů
make strict-types-fix
# nebo zkratka:
make sf
```

---

## Doporučený workflow

### Před commitem

```bash
make all        # Zkontroluje vše
make all-fix    # Opraví automaticky opravitelné problémy
make all        # Ověří, že vše je v pořádku
```

### Průběžný vývoj

```bash
make watch      # Automatický build assetů při změně souborů
make phpstan    # Rychlá kontrola typů po změně PHP souboru
```

### Vytvoření nové funkce

1. Vytvoření migrace (pokud je potřeba databázová změna): `make migrate-create`
2. Spuštění migrace: `make migrate`
3. Implementace kódu (Mapper → Repository → Service → Facade → Presenter/Komponenta)
4. Spuštění testů: `make nette-tester`
5. Kontrola kvality: `make all`

---

## Přehled Make příkazů

### Docker

| Příkaz              | Zkratka | Popis                                    |
|---------------------|---------|------------------------------------------|
| `make up`           | —       | Spuštění aplikace (Docker)               |
| `make down`         | —       | Zastavení aplikace                       |
| `make bash`         | `b`     | Bash v PHP kontejneru                    |
| `make node-bash`    | `nb`    | Bash v Node.js kontejneru                |

### Assety

| Příkaz              | Zkratka | Popis                                    |
|---------------------|---------|------------------------------------------|
| `make build`        | —       | Produkční build assetů                   |
| `make node-build-dev` | `bd` | Vývojový build assetů                    |
| `make watch`        | —       | Watch mód (průběžný build)               |

### Údržba

| Příkaz              | Zkratka | Popis                                    |
|---------------------|---------|------------------------------------------|
| `make delete-cache` | `dc`    | Smazání cache                            |
| `make chmod`        | `cm`    | Nastavení práv na soubory                |

### Testy a kvalita kódu

| Příkaz              | Zkratka | Popis                                    |
|---------------------|---------|------------------------------------------|
| `make all`          | —       | Všechny kontroly                         |
| `make all-fix`      | —       | Automatická oprava všech problémů        |
| `make phpstan`      | `ps`    | Statická analýza                         |
| `make phpcs`        | —       | Kontrola coding standardu               |
| `make phpcs-fix`    | `pf`    | Oprava coding standardu                  |
| `make rector`       | —       | Návrhy refaktoringu (dry-run)            |
| `make rector-fix`   | `rf`    | Aplikace refaktoringu                    |
| `make latte-lint`   | `ll`    | Validace Latte šablon                    |
| `make neon-lint`    | `nl`    | Validace NEON konfigurace                |
| `make nette-tester` | `nt`    | Spuštění testů                           |
| `make strict-types` | —       | Kontrola strict_types (dry-run)          |
| `make strict-types-fix` | `sf`| Přidání strict_types                     |

### Databáze

| Příkaz                | Zkratka | Popis                                  |
|-----------------------|---------|----------------------------------------|
| `make migrate`        | `m`     | Spuštění migrací                       |
| `make migrate-rollback` | `mr`  | Rollback poslední migrace              |
| `make migrate-status` | `ms`    | Stav migrací                           |
| `make migrate-create` | `mc`    | Vytvoření nové migrace                 |
