# Testování

Projekt používá **Nette Tester** — odlehčený testovací framework specificky navržený pro PHP a Nette aplikace.

---

## Spuštění testů

```bash
make nette-tester
# nebo zkratka:
make nt
```

Testy lze také spustit jako součást komplexní kontroly:

```bash
make all        # Spustí testy + linting + statickou analýzu
```

---

## Umístění testů

Testovací soubory jsou v adresáři:

```
tests/
├── bootstrap.php   # Inicializace testovacího prostředí
└── HelloTest.php   # Ukázkový test
```

---

## Struktura testovacího souboru

Každý test musí:
1. Načíst `bootstrap.php`
2. Definovat třídu dědící od `Tester\TestCase`
3. Obsahovat testovací metody s prefixem `test`
4. Na konci souboru spustit instanci: `(new MujTest())->run()`

```php
<?php declare(strict_types=1);

namespace Tests;

require __DIR__ . '/bootstrap.php';

use Tester\Assert;
use Tester\TestCase;

class MujTest extends TestCase
{
    public function testNeco(): void
    {
        Assert::true(true);
    }
}

(new MujTest())->run();
```

---

## Dostupné assertion metody

```php
// Základní assertions
Assert::true($value);                    // $value === true
Assert::false($value);                   // $value === false
Assert::null($value);                    // $value === null
Assert::notNull($value);                 // $value !== null

// Porovnání
Assert::same($expected, $actual);        // Striktní === porovnání
Assert::notSame($expected, $actual);     // Striktní !== porovnání
Assert::equal($expected, $actual);       // Volné == porovnání (rekurzivní pro objekty)

// Typy
Assert::type('string', $value);          // Kontrola typu
Assert::type(User::class, $value);       // Kontrola instance třídy

// Číselné
Assert::count(3, $array);               // Počet prvků

// Výjimky
Assert::exception(function() {
    throw new InvalidArgumentException('test');
}, InvalidArgumentException::class, 'test');

// Vzory (regex)
Assert::match('%a%', $string);           // % je wildcard, %a% = libovolný řetězec
Assert::matchFile('expected.txt', $output);

// Chyby
Assert::error(function() {
    trigger_error('warning', E_USER_WARNING);
}, E_USER_WARNING, 'warning');
```

---

## Metody setUp a tearDown

```php
class DatabaseTest extends TestCase
{
    private MyService $service;

    protected function setUp(): void
    {
        // Spouští se před každou testovací metodou
        $this->service = new MyService();
    }

    protected function tearDown(): void
    {
        // Spouští se po každé testovací metodě
        // (vhodné pro cleanup)
    }

    public function testSomething(): void
    {
        $result = $this->service->doSomething();
        Assert::same('expected', $result);
    }
}
```

---

## Příklad testu doménové třídy

```php
<?php declare(strict_types=1);

namespace Tests\Domain\User;

require __DIR__ . '/../../bootstrap.php';

use App\Domain\User\User;
use App\Domain\UserRole\UserRole;
use Tester\Assert;
use Tester\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User(
            id: 1,
            email: 'test@example.com',
            passwordHash: 'hash',
            role: UserRole::User,
            active: true,
            lastLogin: null,
        );

        Assert::same(1, $user->id);
        Assert::same('test@example.com', $user->email);
        Assert::true($user->active);
        Assert::null($user->lastLogin);
    }

    public function testIsAdmin(): void
    {
        $adminUser = new User(
            id: 1,
            email: 'admin@example.com',
            passwordHash: 'hash',
            role: UserRole::Admin,
            active: true,
            lastLogin: null,
        );

        Assert::same(UserRole::Admin, $adminUser->role);
    }
}

(new UserTest())->run();
```

---

## Konvence pojmenování testů

- Testovací soubor odpovídá testované třídě: `UserService` → `UserServiceTest.php`
- Testovací metody popisují co testují: `testGetUserByIdThrowsExceptionWhenNotFound()`
- Adresářová struktura v `tests/` by měla zrcadlit strukturu `app/`

```
tests/
├── bootstrap.php
├── Domain/
│   └── User/
│       ├── UserTest.php
│       └── UserServiceTest.php
└── Presentation/
    └── ...
```

---

## Bootstrap soubor

`tests/bootstrap.php` inicializuje testovací prostředí:

```php
<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
```

`Tester\Environment::setup()` nastaví PHP error handler pro Nette Tester, vypne output buffering a nastaví vhodné výchozí hodnoty pro testování.
