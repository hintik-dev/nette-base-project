# DataGrid

Projekt obsahuje vlastní vrstvu nad [ublaboo/datagrid](https://github.com/contributte/datagrid) (`Contributte\Datagrid`), přizpůsobenou vzhledu Admin modulu (AdminLTE + Bootstrap 5). Gridy se vytvářejí jako běžné [komponenty](components.md), jen dědí od `BaseGridComponent` místo `BaseComponent`.

---

## Struktura grid komponenty

Stejná jako u běžné komponenty — tři soubory ve společném adresáři:

```
app/Presentation/Components/{Modul}/{Sekce}/{Název}Grid/
├── {Název}Grid.php           # Hlavní třída (extends BaseGridComponent)
├── {Název}Grid.latte         # {control grid}
└── {Název}GridFactory.php    # Interface factory
```

**Příklad — seznam uživatelů:**

```
app/Presentation/Components/Admin/User/UserListGrid/
├── UserListGrid.php
├── UserListGrid.latte
└── UserListGridFactory.php
```

---

## BaseGridComponent

`App\Presentation\Components\Base\BaseGridComponent` řeší vše společné pro všechny gridy v aplikaci — vytvoření `BaseGrid` instance přes `BaseGridFactory` a nastavení translatoru. Konkrétní grid komponenta jen implementuje `configureGrid()`:

```php
<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\User\UserListGrid;

use App\Domain\User\ExplorerUserRepository;
use App\Domain\User\UserFacade;
use App\Presentation\Components\Base\BaseGridComponent;
use App\Presentation\Control\DataGrid\BaseGrid;
use App\Presentation\Control\DataGrid\BaseGridFactory;

class UserListGrid extends BaseGridComponent
{
    public function __construct(
        private readonly UserFacade $userFacade,
        BaseGridFactory $baseGridFactory,
    ) {
        parent::__construct($baseGridFactory);
    }

    protected function configureGrid(BaseGrid $grid): void
    {
        $grid->setDataSource($this->userFacade->getAllUsersDataSource());

        $grid->addColumnNumber(ExplorerUserRepository::COLUMN_ID, 'ID')
            ->setFitContent();

        $grid->addColumnText(ExplorerUserRepository::COLUMN_EMAIL, 'E-mail')
            ->setSortable()
            ->setFilterText();

        $grid->setDefaultSort([ExplorerUserRepository::COLUMN_ID => 'ASC']);
    }
}
```

**Nikdy nevolejte `new BaseGrid()` ani `$grid->setTranslator(...)` ručně** — o to se stará `BaseGridComponent::createComponentGrid()`. Stačí naplnit `$grid` uvnitř `configureGrid()` a vrátit ho není třeba (metoda má návratový typ `void`, grid se nastavuje mutací).

Šablona komponenty jen vykreslí vnořený grid:

```latte
{* UserListGrid.latte *}
{control grid}
```

---

## Zdroj dat

`setDataSource()` přijímá `Nette\Database\Table\Selection` — repozitář vrací výsledek `findAll()`/`getTable()` bez dalšího obalování:

```php
// v repozitáři
/** @return Selection<ActiveRow> */
public function getAllSelection(): Selection
{
    return parent::findAll();
}
```

```php
// v grid komponentě
$grid->setDataSource($this->userFacade->getAllUsersDataSource());
```

---

## Sloupce

| Metoda | Použití |
|---|---|
| `addColumnText($key, $name)` | textový sloupec, podporuje `setFilterText()`, `setSortable()` |
| `addColumnNumber($key, $name)` | číselný sloupec (zarovnání doprava) |
| `addColumnDateTime($key, $name)` | datum/čas, `setFormat('d.m.Y H:i:s')` |
| `addColumnDateTimeFromTimestamp($key, $name)` | datum/čas z UNIX timestampu (i v milisekundách přes `withMillis: true`) |
| `addColumnStatus($key, $name)` | odznak se stavy (`addOption(...)->setClass(...)->endOption()`) |

Vlastní vykreslení buňky přes `setRenderer()`:

```php
$grid->addColumnText(ExplorerUserRepository::COLUMN_ROLE, 'Role')
    ->setRenderer(fn($row) => UserRole::from($row[ExplorerUserRepository::COLUMN_ROLE])->toLabel())
    ->setFitContent();
```

`setFitContent()` zúží sloupec na obsah (vhodné pro ID, stavy, akce).

---

## Filtry

```php
$grid->addColumnText(ExplorerUserRepository::COLUMN_EMAIL, 'E-mail')
    ->setFilterText();

$grid->addFilterSelect('role', 'Role', [
    'admin' => 'Administrátor',
    'user'  => 'Uživatel',
]);
```

Pro rozsahové/datumové filtry je k dispozici `DateTimeRangeFilterTrait` (součást `BaseGrid`) — např. `addFilterDateTimeRangeFromDatetime()`.

---

## Akce

```php
$grid->addActionCallback(
    'runNow',
    'Spustit hned',
    function (string $id): void {
        $this->presenter->redirect('runNow!', ['id' => (int) $id]);
    },
)->setIcon('play-fill')->setClass('btn btn-sm btn-outline-success');
```

Ikony (`setIcon(...)`) používají prefix `bi bi-` ([Bootstrap Icons](https://icons.getbootstrap.com/)) — `BaseGrid::$iconPrefix` lze v případě potřeby změnit globálně.

---

## Vykreslení v presenteru

Stejně jako u jiných komponent — registrace v presenteru a `{control}` v šabloně:

```php
class UserPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly UserListGridFactory $userListGridFactory,
    ) {
        parent::__construct();
    }

    public function createComponentUserListGrid(): UserListGrid
    {
        return $this->userListGridFactory->create();
    }
}
```

```latte
{* list.latte *}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Uživatelé</h3>
    </div>
    <div class="card-body p-0">
        {control userListGrid}
    </div>
</div>
```

> `card-body` musí mít třídu `p-0` — grid má vlastní padding, dvojitý padding karty by vzhled rozbil.

---

## Jazykové mutace

Vestavěné texty datagridu (stránkování, "žádné položky nenalezeny" apod.) jsou přeloženy přes `contributte/translation` v `app/Lang/contributte_datagrid.cs_CZ.neon`. Bez zaregistrovaného translatoru (což `BaseGridComponent` řeší automaticky) by se zobrazovaly anglické texty nebo nepřeložené klíče jako `contributte_datagrid.items_per_page`. Více v [Jazykové mutace](translations.md).

---

## Šablony datagridu

Vzhled samotné tabulky (AdminLTE/Bootstrap 5 styl, filtry, stránkování, potvrzovací modal) je definován v `app/Presentation/Control/DataGrid/templates/`. Tyto šablony se běžně neupravují — případné vzhledové úpravy patří sem, ne do šablon konkrétních presenterů.
