# Jazykové mutace

Projekt používá [contributte/translation](https://github.com/contributte/translation) pro lokalizaci textů, které framework nebo knihovny generují samy (typicky [DataGrid](datagrid.md)). Aplikační texty (obsah šablon, popisky formulářů) se běžně píšou přímo v šabloně/kódu v češtině — translator slouží pro místa, kam nelze text napsat přímo, protože ho generuje cizí knihovna.

---

## Konfigurace

Registrace v `config/common.neon`:

```neon
extensions:
    translation: Contributte\Translation\DI\TranslationExtension

translation:
    locales:
        whitelist: [cs_CZ]
        default: cs_CZ
        fallback: [cs_CZ]
    dirs:
        - %appDir%/Lang
    returnOriginalMessage: true
```

Projekt je jednojazyčný (`cs_CZ`) — pokud přibude potřeba více jazyků, doplňte je do `whitelist` a zvažte přidání `localeResolvers` (např. `Contributte\Translation\LocalesResolvers\Session` pro přepínání jazyka v session).

`returnOriginalMessage: true` znamená, že chybějící klíč se vypíše tak, jak je zapsaný v kódu (např. `contributte_datagrid.items_per_page`), místo aby vyhodil chybu — usnadňuje to odhalení chybějícího překladu.

---

## Překladové soubory

Umístění: `app/Lang/{namespace}.{locale}.neon`, např.:

```
app/Lang/contributte_datagrid.cs_CZ.neon
```

Obsah je plochý seznam klíč → text:

```neon
no_item_found: 'Žádné položky nenalezeny.'
items_per_page: 'Záznamů na stránce:'
```

Klíč v překladu se používá s prefixem podle názvu souboru: `contributte_datagrid.no_item_found`.

---

## Použití v komponentě

Vstřikněte `Contributte\Translation\Translator` (implementuje `Nette\Localization\Translator`) a zavolejte `translate()`:

```php
public function __construct(
    private readonly Translator $translator,
) {
}

public function render(): void
{
    $this->getTemplate()->title = $this->translator->translate('front.homePresenter.title');
}
```

V [DataGridu](datagrid.md) translator nastavuje automaticky `BaseGridComponent` — vlastní grid komponenty ho není potřeba injektovat ani nastavovat ručně.

---

## Použití v šabloně

Latte filtr `|translate` (registruje ho automaticky Nette při renderu, viz `Template::setTranslator()`):

```latte
{='contributte_datagrid.no_item_found'|translate}
```

> Poznámka: standalone `php latte-lint` (spouštěné přes `make latte-lint`) hlásí u `|translate` a podobných za běhu svázaných konstrukcí (`{form}`, `isLinkCurrent()`) falešně pozitivní varování — lint si sestavuje holý `Latte\Engine` bez kontextu skutečného presenteru/komponenty. Za běhu aplikace tyto konstrukce fungují správně.
