# Databázové migrace

Projekt používá **Phinx** pro správu databázových migrací. Migrace umožňují verzovat strukturu databáze a aplikovat změny konzistentně ve všech prostředích.

---

## Základní příkazy

### Spuštění migrací

Aplikuje všechny dosud nespuštěné migrace:

```bash
make migrate
# nebo zkratka:
make m
```

### Stav migrací

Zobrazí seznam všech migrací a jejich stav (spuštěna / nespuštěna):

```bash
make migrate-status
# nebo zkratka:
make ms
```

### Rollback poslední migrace

Vrátí zpět poslední spuštěnou migraci:

```bash
make migrate-rollback
# nebo zkratka:
make mr
```

### Vytvoření nové migrace

Interaktivně se zeptá na název a vytvoří soubor migrace:

```bash
make migrate-create
# nebo zkratka:
make mc
```

Zadejte název ve formátu **CamelCase**, např. `CreateArticleTable` nebo `AddColumnToUserTable`.

---

## Umístění migrací

Migrační soubory jsou uloženy v:

```
db/migrations/
```

Název souboru má formát `{timestamp}_{název_v_snake_case}.php`, např.:
```
db/migrations/20260225174154_create_user_table.php
```

---

## Struktura migračního souboru

Phinx generuje třídu s metodou `change()`, která podporuje **automatický rollback** (reverzibilní migrace):

```php
<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateArticleTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('article', ['comment' => 'Články']);
        $table
            ->addColumn('title', 'string', ['limit' => 255, 'null' => false, 'comment' => 'Titulek článku'])
            ->addColumn('content', 'text', ['null' => false, 'comment' => 'Obsah článku'])
            ->addColumn('author_id', 'integer', ['null' => false, 'comment' => 'FK uživatel (autor)'])
            ->addColumn('published_at', 'datetime', ['null' => true, 'default' => null, 'comment' => 'Datum publikování, null = nepublikováno'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => 'Datum vytvoření záznamu'])
            ->addForeignKey('author_id', 'user', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->create();
    }
}
```

> Ke každému sloupci (včetně automaticky generovaného `id`) přidávejte klíč `'comment'` s krátkým popisem. Komentář se uloží přímo do schématu databáze a usnadňuje orientaci v tabulkách bez nutnosti číst kód migrace.

---

## Typy sloupců

| Phinx typ    | SQL typ                  | Příklad options                              |
|--------------|--------------------------|----------------------------------------------|
| `string`     | VARCHAR                  | `['limit' => 255]`                           |
| `text`       | TEXT                     | —                                            |
| `integer`    | INT                      | `['signed' => false]`                        |
| `biginteger` | BIGINT                   | —                                            |
| `float`      | FLOAT                    | —                                            |
| `decimal`    | DECIMAL                  | `['precision' => 10, 'scale' => 2]`          |
| `boolean`    | TINYINT(1)               | `['default' => true]`                        |
| `datetime`   | DATETIME                 | `['null' => true]`                           |
| `date`       | DATE                     | —                                            |
| `time`       | TIME                     | —                                            |
| `enum`       | ENUM                     | `['values' => ['admin', 'user']]`            |
| `uuid`       | CHAR(36)                 | —                                            |

---

## Příklady operací

### Přidání sloupce do existující tabulky

```php
public function change(): void
{
    $table = $this->table('user');
    $table
        ->addColumn('phone', 'string', ['limit' => 20, 'null' => true, 'default' => null])
        ->update();
}
```

### Přejmenování sloupce

```php
public function change(): void
{
    $table = $this->table('user');
    $table
        ->renameColumn('phone', 'phone_number')
        ->update();
}
```

### Přidání indexu

```php
public function change(): void
{
    $table = $this->table('article');
    $table
        ->addIndex(['author_id'])
        ->addIndex(['title'], ['unique' => true])
        ->update();
}
```

### Zrušení tabulky

```php
public function change(): void
{
    $this->table('old_table')->drop()->save();
}
```

---

## Ireverzibilní migrace (up/down)

Pokud migrace nelze automaticky vrátit (např. modifikace dat), použijte metody `up()` a `down()`:

```php
public function up(): void
{
    // Aplikace změny
    $this->execute(<<<SQL
        UPDATE user SET role = 'admin' WHERE email = 'admin@example.com';
        SQL);
}

public function down(): void
{
    // Vrácení změny
    $this->execute(<<<SQL
        UPDATE user SET role = 'user' WHERE email = 'admin@example.com';
        SQL);
}
```

Při přímém SQL dotazu přes `$this->execute()` vždy používejte **heredoc syntaxi** `<<<SQL ... SQL` místo řetězce v uvozovkách. Výsledný SQL kód je tak přehledný a snadno rozšiřitelný.

---

## Konfigurace (phinx.php)

Phinx je nakonfigurován v `phinx.php` v kořeni projektu. Připojení k databázi čte z proměnných prostředí:

```php
'host' => getenv('DB_HOST') ?: 'database',
'name' => getenv('DB_NAME') ?: 'database',
'user' => getenv('DB_USER') ?: 'root',
'pass' => getenv('DB_PASSWORD') ?: 'root_pristup_123',
'charset' => 'utf8mb4',
```
