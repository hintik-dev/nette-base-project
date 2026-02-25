# Spuštění projektu

## Požadavky

- Docker a Docker Compose
- Make
- Node.js (volitelně, pokud chcete buildovat assety mimo Docker)

---

## 1. Klonování repozitáře

```bash
git clone https://github.com/hintik-dev/nette-base-project.git
cd nette-base-project
```

---

## 2. Konfigurace prostředí

### Docker proměnné

Zkopírujte vzorové soubory prostředí:

```bash
cp docker/.env_example docker/.env
cp docker/php.env_example docker/php.env
```

Upravte `docker/.env` podle potřeby:

```dotenv
COMPOSE_PROJECT_NAME="hintik-base-web"
PORT_APACHE=10000        # Port pro webový server (http://localhost:10000)
PORT_MARIADB=20000       # Port pro MariaDB
PORT_PHP_MY_ADMIN=30000  # Port pro phpMyAdmin (http://localhost:30000)
```

### Konfigurace databáze

Zkopírujte vzorový konfigurační soubor databáze:

```bash
cp config/local.example/database.neon config/local/database.neon
```

---

## 3. Spuštění kontejnerů

```bash
make up
```

Tímto se spustí všechny Docker kontejnery:
- **PHP/Apache** na portu 10000
- **MariaDB** na portu 20000
- **phpMyAdmin** na portu 30000
- **Node.js** (idle kontejner pro npm příkazy)

> Při prvním spuštění se automaticky spustí `composer install` uvnitř PHP kontejneru.

---

## 4. Nastavení práv

Zapísatelné složky `temp/` a `log/` musí mít práva pro zápis:

```bash
make chmod
```

---

## 5. Spuštění databázových migrací

```bash
make migrate
```

---

## 6. Build assetů

### Vývojový build (rychlejší)

```bash
make node-build-dev
# nebo zkratka:
make bd
```

### Produkční build

```bash
make build
```

### Watch mód (průběžný build při změnách)

```bash
make watch
```

---

## 7. Ověření funkčnosti

Otevřete v prohlížeči:
- **Aplikace:** http://localhost:10000
- **Admin sekce:** http://localhost:10000/admin
- **phpMyAdmin:** http://localhost:30000

---

## Přehled přihlašovacích údajů (výchozí)

| Služba      | Uživatel | Heslo             |
|-------------|----------|-------------------|
| MariaDB     | root     | root_pristup_123  |
| phpMyAdmin  | root     | root_pristup_123  |

---

## Práce v PHP kontejneru

Pro spouštění příkazů uvnitř PHP kontejneru:

```bash
make bash
# nebo zkratka:
make b
```

---

## Zastavení aplikace

```bash
make down
# nebo:
make stop
```

---

## Smazání cache

Pokud dojde k problémům s cache:

```bash
make delete-cache
# nebo zkratka:
make dc
```
