# ACL — role a oprávnění

> **Stav:** návrh pro verzi 1.2, implementace probíhá ve větvi `feature/1.2-acl`.
> Tento dokument je živý — udržuje se v průběhu implementace a po dokončení
> zůstává jako referenční dokumentace k systému oprávnění.

Aplikace přechází ze statického ACL zapsaného v PHP souboru na **role a oprávnění spravovaná v databázi**. Cílem je odstranit rozhodování podle role — prakticky vše se má zobrazovat a fungovat podmíněně podle konkrétního oprávnění.

---

## Obsah

- [Výchozí stav](#výchozí-stav)
- [Klíčový technický nález](#klíčový-technický-nález)
- [Datový model](#datový-model)
- [Registr oprávnění](#registr-oprávnění)
- [Skládání podle priority](#skládání-podle-priority)
- [Vlastnická oprávnění](#vlastnická-oprávnění)
- [Okamžitý dopad změn](#okamžitý-dopad-změn)
- [Ochrana proti zamčení se ven](#ochrana-proti-zamčení-se-ven)
- [Dopady na aplikaci](#dopady-na-aplikaci)
- [Rozhodnutá zadání](#rozhodnutá-zadání)
- [Rizika](#rizika)
- [Fázování a stav implementace](#fázování-a-stav-implementace)

---

## Výchozí stav

Před změnou je ACL jeden soubor. `App\Model\Security\Authorizator\StaticAuthorizator` dědí z `Nette\Security\Permission`, registruje řetěz rolí `guest → user → admin → superadmin`, zavolá `allow()` pro všechny a udělá jedinou výjimku pro `Page`. Role uživatele je enum v ENUM sloupci `user.role` — tedy právě jedna role na uživatele.

Vynucování ale sedí na správné vrstvě — ve **fasádách**. `UserFacade`, `PageFacade` a `ValueStorageFacade` volají `$securityUser->isAllowed($resource, $privilege)` a házejí `InsufficientPrivilegesException`. Nový model se na tento styl napojí beze změny volání.

Tři mezery, které je nutné započítat do rozsahu — nejde o refaktoring, ale o práci navíc:

- **Šablony neřeší oprávnění vůbec.** V žádném `.latte` souboru není jediné volání `isAllowed`. Sidebar zobrazuje všechny položky každému.
- **`InsufficientPrivilegesException` se nikde nechytá.** Mimo fasády na ni není reference, takže dnes končí jako chyba 500. Šablona `app/Presentation/Error/Error4xx/403.latte` přitom existuje.
- **Presentery nekontrolují nic nad rámec přihlášení.** `BaseAdminPresenter::checkRequirements()` jen přesměruje nepřihlášené.

---

## Klíčový technický nález

Tento nález určuje tvar celého řešení. `Nette\Security\User::isAllowed()` vypadá takto:

```php
public function isAllowed($resource = null, $privilege = null): bool
{
    foreach ($this->getRoles() as $role) {
        if ($this->getAuthorizator()->isAllowed($role, $resource, $privilege)) {
            return true;   // ← první povolující role vyhrává
        }
    }
    return false;
}
```

Je to **čisté OR přes role, po jedné**. Autorizátor nikdy neuvidí celou sadu rolí najednou, takže se do rozhodnutí nedostane ani třístavovost (deny by nemohl přebít allow z jiné role), ani priority.

**Řešení:** `App\Model\Security\SecurityUser` už existuje jako potomek `Nette\Security\User` a je zaregistrovaný jako `security.user`. Přepíše se v něm `isAllowed()` a předá celou sadu rolí vlastnímu evaluátoru. Veřejné API `$user->isAllowed(...)` zůstává, takže fasády a šablony volají dál to samé.

`Nette\Security\Permission` se **zahazuje**, nepředělává. Vyžaduje předregistraci všech rolí, resources i privilegií a na neznámé hodnoty hází `InvalidStateException`, což se s daty z databáze snáší špatně; jeho dědičnost má navíc váhovou sémantiku podle pořadí zápisu, ne prioritní. Interface `Nette\Security\Authorizator` se implementuje kvůli kompatibilitě, ale skutečný výpočet dělá vlastní služba.

Evaluátor přijímá **ID uživatele**, ne přihlášeného uživatele ze `SecurityUser`. Díky tomu ho lze zavolat i v `UserAuthenticator` ještě před přihlášením — viz [Okamžitý dopad změn](#okamžitý-dopad-změn).

---

## Datový model

Tři nové tabulky a jedna úprava stávající. Migrace přes Phinx, viz [docs/migrations.md](migrations.md).

### `user_role`

| Sloupec | Typ | Poznámka |
|---|---|---|
| `id` | int | PK |
| `code` | string(64) | unique, strojový klíč (`default`, `editor`, …) |
| `name` | string(255) | zobrazovaný název |
| `description` | string(255) | null |
| `priority` | int unsigned | **unique**, `0` = výchozí role |
| `is_system` | bool | nelze smazat ani přejmenovat |
| `created_at` | datetime | |

### `user_role_permission`

| Sloupec | Typ | Poznámka |
|---|---|---|
| `id` | int | PK |
| `user_role_id` | int | FK → `user_role`, ON DELETE CASCADE |
| `permission_key` | string(190) | klíč z registru, bez FK |
| `effect` | enum | `allow` / `deny` |

Unique index na `(user_role_id, permission_key)`.

Zaznamenávají se **jen stavy `allow` a `deny`**; neexistence řádku znamená neutralitu. Je to řídký zápis — drží tabulku malou a „vyčištění na neutrál“ je smazání řádku.

### `user_x_user_role`

| Sloupec | Typ | Poznámka |
|---|---|---|
| `user_id` | int | FK → `user`, ON DELETE CASCADE |
| `user_role_id` | int | FK → `user_role`, ON DELETE CASCADE |

Složený PK `(user_id, user_role_id)`.

### Úprava `user`

- ruší se ENUM sloupec `role`
- přidává se `is_superadmin` bool — bypass ACL, viz [Ochrana proti zamčení se ven](#ochrana-proti-zamčení-se-ven)

### Proč není tabulka `permission`

Oprávnění nejsou volná data — každý klíč musí někde v kódu někdo kontrolovat. Uživatelem vymyšlené oprávnění, na které se nikdo neptá, je jen řádek v tabulce. Zdrojem pravdy proto zůstává **registr v PHP** a databáze drží jen přiřazení jako textový klíč, bez cizího klíče.

Refaktoring oprávnění pak nevyžaduje migraci, deploy nepotřebuje synchronizační krok a klíče, které registr nezná, se při vyhodnocení ignorují (v adminu se vypíšou jako osiřelé k úklidu).

---

## Registr oprávnění

Klíč má tvar `entita.akce`, případně `entita.akce.scope` pro práva vázaná na vlastnost. Klíče se scope jsou v tabulce uvedené kvůli formátu — implementuje je až fáze 4, do registru se přidají s ní.

| Klíč | Entita | Význam |
|---|---|---|
| `admin.access` | Administrace | Přístup do administrace vůbec — bez něj se nelze přihlásit |
| `user.list` | Uživatel | Zobrazit seznam uživatelů |
| `user.change-password` | Uživatel | Změnit heslo libovolnému uživateli |
| `user.change-password.own` | Uživatel | Změnit vlastní heslo (fáze 4) |
| `page.publish` | Stránka | Publikovat stránku |
| `page.delete` | Stránka | Smazat libovolnou stránku |
| `page.delete.own` | Stránka | Smazat vlastní stránku (fáze 4) |
| `acl.role.edit` | Role | Spravovat role a jejich oprávnění |
| `acl.role.assign` | Role | Přiřazovat role uživatelům |

Definice patří k doméně, ne do jednoho velkého seznamu. Každý balík v `app/Domain/` přispěje vlastním enumem — projekt už má pro enumy s popisky zavedený vzor:

```php
enum PagePermission: string implements PermissionDefinition
{
    case ListAll  = 'page.list';
    case Edit     = 'page.edit';
    case EditOwn  = 'page.edit.own';
    case Publish  = 'page.publish';

    public function getLabel(): string { ... }   // popisek v admin UI
    public function getGroup(): string { ... }   // seskupení = entita
    public function getScope(): ?string { ... }  // null | 'own'
}
```

`PermissionRegistry` je posbírá a slouží dvěma věcem: staví matici v admin UI seskupenou po entitách a validuje klíče. Vedlejším efektem je typová bezpečnost — `isAllowed(PagePermission::Edit)` místo řetězce, který PHPStan nezkontroluje.

### Klíč vs. název formulářového prvku

Matice oprávnění dělá z každého klíče formulářový prvek, jenže `Nette\ComponentModel\Container` povoluje v názvu komponenty pouze `[a-zA-Z0-9_]`. Klíče jsou přitom kebab-case oddělený tečkami (`user.change-password`), takže je nutné zakódovat tečku i pomlčku — dělá to `PermissionControlName` (tečka → `__`, pomlčka → `_`).

Chybu tohoto typu nezachytí PHPStan ani latte-lint, protože název prvku je jen řetězec. Hlídá ji proto test, který prožene všechny klíče z registru stejným regulárním výrazem, jaký používá Nette — nové oprávnění s neplatným klíčem tak spadne v testech, ne až na stránce.

---

## Skládání podle priority

Sada rolí uživatele = **výchozí role** (priorita `0`) plus jeho přiřazené role, seřazená **vzestupně podle priority**. Vrstvy se aplikují postupně; explicitní záznam ve vyšší prioritě přepíše nižší. Neutrál nic nepřepisuje. Výsledný neutrál znamená zákaz — *default deny*.

Výchozí roli **nelze explicitně přiřadit**, aplikuje se vždy — i uživatelům bez jakékoli role.

Příklad složení pro uživatele s rolemi Redaktor a Moderátor:

| Vrstva | priorita | `page.edit` | `page.delete` | `user.list` |
|---|---:|---|---|---|
| Výchozí role | 0 | neutral | **deny** | neutral |
| Redaktor | 10 | **allow** | **allow** | neutral |
| Moderátor | 20 | neutral | **deny** | neutral |
| **Výsledek** | | **allow** | **deny** | **deny** |

- `page.edit` — neutrál z vyšší role nepřepisuje allow z nižší.
- `page.delete` — deny z vyšší priority přebije allow z nižší; fungovalo by to i obráceně.
- `user.list` — *default deny*: nikdo nic neřekl, takže zákaz.

Tento model („vrstvené přepisování“) je jediný, ve kterém priorita skutečně něco znamená. Alternativa „deny vyhrává vždy bez ohledu na prioritu“ je jednodušší na pochopení, ale pak by nešlo udělat roli, která záměrně odblokuje něco zakázaného níž — a čísla priorit by byla jen ozdoba.

**Priority jsou unikátní.** Tím zcela odpadá otázka, co se stane při shodné prioritě dvou rolí s protichůdným nastavením, a admin UI může nabídnout přeskládání pořadí místo psaní čísel.

Nad tím vším stojí **superadmin bypass**: uživatel s `is_superadmin` dostane z `isAllowed()` vždy `true` bez dotazu do ACL.

---

## Vlastnická oprávnění

„Smazat můj příspěvek“ nejde vyhodnotit ze samotné databáze — potřebuje konkrétní entitu a pravidlo, co znamená „můj“. Pravidlo patří do kódu, přiřazení do databáze:

```php
interface ScopeResolver
{
    public function supports(string $resource, string $scope): bool;
    public function matches(object $entity, int $userId): bool;
}
```

Vyhodnocení jednoho dotazu proběhne ve dvou krocích:

1. Zkusí se **globální** klíč (`page.delete`). Když projde, hotovo.
2. Jinak se zkusí **scoped** klíč (`page.delete.own`) — ten uspěje jen tehdy, když ho ACL povolí *a zároveň* resolver potvrdí vlastnictví.

Volání se rozšíří o třetí parametr:

```php
$user->isAllowed(Page::RESOURCE_ID, 'delete', $page);
```

### Omezení u výpisů

U **výpisů** scoped práva tímto způsobem nefungují — nelze načíst deset tisíc stránek a filtrovat je v PHP. Fasáda musí rozhodnout před dotazem: má uživatel globální `page.list`? Vrátí celý `Selection`. Má jen `page.list.own`? Vrátí ho s `where('author_id', $userId)`.

To je ruční práce pro každý grid a je to největší zdroj rozsahu. **Ve verzi 1.2 se scope podporuje jen u operací nad jednou entitou** (detail, edit, delete). Formát klíčů se ale navrhuje rovnou i pro výpisy, aby se nemusel později měnit. Obecný mechanismus pro filtrování výpisů je samostatná feature.

---

## Okamžitý dopad změn

Odebrání role nebo oprávnění se musí projevit okamžitě, ne až po opětovném přihlášení.

Architektura projektu to splňuje prakticky zdarma — `DbUserStorage::getState()` už dnes při každém requestu sáhne do databáze pro uživatele a přes `IdentityFactory` postaví identitu znovu. V cookie je jen náhodný token. Stačí to nepokazit:

1. **Role ani oprávnění nesmí být v identitě, session ani cookie.** `IdentityFactory` dnes vkládá `[$user->role->value]` do identity; v novém modelu se z identity nesmí rozhodovat.
2. **Cache jen v rámci requestu.** Evaluátor drží spočtenou efektivní sadu práv v paměti po dobu requestu. Žádná perzistentní `Nette\Caching` vrstva — nebo pokud ano, tak s tagy a invalidací při každé změně role, oprávnění i přiřazení.
3. **Vynucení zůstává ve fasádách.** I kdyby uživateli v prohlížeči zůstala vyrenderovaná stránka s tlačítkem, které už nemá mít, odeslání formuláře selže na kontrole ve fasádě.

Cena je jeden dotaz navíc na request (join přes `user_x_user_role`, `user_role` a `user_role_permission` plus řádky výchozí role). Zanedbatelné.

Protože se práva vyhodnocují při každém requestu z databáze, **není potřeba žádný mechanismus, který by sahal do cizích sessions**. Změna se projeví sama u dalšího requestu dotčeného uživatele.

### Tři úrovně reakce

Ztráta oprávnění nemá vždy stejný důsledek. Podle toho, co uživatel ztratil, se aplikace zachová jinak:

#### Úroveň 0 — ztráta běžného oprávnění

Navenek se neděje nic. UI přestane akci nabízet, fasáda ji přestane pouštět. Uživatel zůstává tam, kde je.

#### Úroveň 1 — ztráta přístupu na aktuální stránku

Uživatele odešleme pryč. Řeší to atribut na presenteru nebo akci, vyhodnocený v `checkRequirements()`:

```php
#[RequiresPermission(PagePermission::ListAll)]
public function actionDefault(): void
```

Protože `checkRequirements()` běží při každém requestu, uživatel, kterému bylo právo odebráno, na stránce nezůstane déle než do dalšího requestu. Při selhání kontroly **nenásleduje chyba 403, ale přesměrování** na `:Admin:Home:default` s flash zprávou — je to vstřícnější a odpovídá to zadání „pošleme ho pryč“.

Chyba 403 zůstává pro `InsufficientPrivilegesException`, která unikne z fasády (typicky u signálů a odeslání formulářů). To je záchytná síť, ne primární cesta.

Dashboard `:Admin:Home:default` proto **nesmí vyžadovat nic než `admin.access`**, aby byl vždy platným cílem přesměrování. Tím je zároveň ošetřena smyčka: pokud by selhal i dashboard, znamená to chybějící `admin.access` a nastupuje úroveň 2.

#### Úroveň 2 — ztráta přístupu do administrace

Oprávnění `admin.access` je zvláštní — bez něj se uživatel nemá kam přesměrovat. Kontroluje se na dvou místech:

- **Při přihlášení** v `UserAuthenticator::authenticate()` — přihlášení se odmítne. Proto evaluátor přijímá ID uživatele a ne přihlášeného `SecurityUser`.
- **Při každém requestu** v `BaseAdminPresenter::checkRequirements()`, ještě před kontrolou atributu z úrovně 1.

Při selhání se session **vynuceně ukončí**: záznam v `user_session` se označí jako odhlášený, cookie se smaže a uživatel je přesměrován na přihlašovací stránku s vysvětlením.

Infrastruktura pro to existuje — `ExplorerUserSessionRepository::markAsLoggedOut()` a enum `LogoutReason`. `DbUserStorage::clearAuthentication()` dnes zapisuje napevno `LogoutReason::Manual`, takže potřebuje doplnit možnost předat důvod. Doporučuje se přidat i nový případ `LogoutReason::AccessRevoked` kvůli auditu — pozor, `UserSessionGrid` má nad tímto enumem `match`, který by jinak spadl na `UnhandledMatchError`.

Ruční vynucené odhlášení uživatele administrátorem už existuje (`UserSessionFacade` používá `LogoutReason::Forced`) a zůstává samostatnou akcí — odebrání role samo o sobě uživatele neodhlašuje, pokud mu nezmizí `admin.access`.

---

## Ochrana proti zamčení se ven

Jakmile je oprávnění editovat oprávnění samo uloženo v databázi, vzniká scénář, kdy si administrátor jedním kliknutím vezme přístup ke správě práv a nikdo ho nemůže vrátit. Tři vrstvy obrany:

- **Bypass flag.** `user.is_superadmin` obchází ACL úplně. Nezávisí na datech v tabulkách rolí, takže ho nelze rozbít z admin rozhraní — a nastavit ho lze jen z CLI (`ExplorerUserRepository::setSuperadmin()` nemá cestu z formulářů), takže si obejití oprávnění nikdo neudělí sám.
- **Recovery přes CLI.** Příkaz `app:create-superadmin` (viz [docs/commands.md](commands.md)) zůstává cestou ven i z úplně rozbitého stavu.
- **Guardy ve fasádě.** Nelze si odebrat vlastní `acl.*` oprávnění. Nelze smazat ani odebrat oprávnění poslední roli, která dává `acl.role.edit`. Výchozí roli nelze smazat, změnit jí prioritu ani ji explicitně přiřadit.

---

## Dopady na aplikaci

| Místo | Co je potřeba |
|---|---|
| `SecurityUser` | Přepsat `isAllowed()`, delegovat na vlastní evaluátor s celou sadou rolí; přidat volitelný parametr pro entitu |
| `IdentityFactory` | Přestat rozhodovat podle role vložené do identity |
| `UserAuthenticator` | Odmítnout přihlášení bez `admin.access` |
| `DbUserStorage` | Umožnit předat `LogoutReason` do `clearAuthentication()` |
| `sidebar.latte` | Každá položka menu pod `n:if` podle oprávnění — dnes se zobrazuje vše všem |
| `LatteExtension` | `getFunctions()` je prázdné a připravené; přidat funkci `isAllowed()` kvůli scoped kontrolám s entitou. `SecurityUser` je v šablonách už dostupný jako `$_user` přes `BaseTemplate` |
| `BaseAdminPresenter` | Kontrola `admin.access` a atributu `#[RequiresPermission]` v `checkRequirements()` |
| `Error4xx` | Převést `InsufficientPrivilegesException` na 403 — šablona existuje, handling ne |
| Fasády | Nahradit řetězce enum konstantami, zjemnit granularitu akcí |
| `UserForm`, `UserListGrid` | Select jedné role → multiselect rolí přes pivot |
| Nové admin UI | CRUD rolí s prioritou + třístavová matice oprávnění seskupená po entitách |
| `CreateSuperAdminCommand` | Přepnout z enum role na `is_superadmin` |
| Api modul | Beze změny. `ApiUser` má vlastní tokenovou autentizaci a ACL se ho netýká — role pro API uživatele jsou samostatná úvaha |

---

## Rozhodnutá zadání

| Otázka | Rozhodnutí |
|---|---|
| Unikátní priority rolí? | **Ano.** Odpadá tiebreak, admin UI nabídne přeskládání pořadí. |
| Vlastnická práva už v 1.2? | **Částečně.** Klíče a resolvery navrhnout hned, implementovat jen nad jednou entitou. Filtrování výpisů odloženo. |
| Sloupec `user.role`? | **Nahradit `is_superadmin`.** Ponechaný ENUM sloupec by lákal k dalšímu rozhodování podle role. |
| Tabulka `permission`? | **Ne.** Jen registr v kódu, klíče jako řetězce bez FK. |
| Odhlášení při odebrání role? | **Jen při ztrátě `admin.access`.** Ztráta přístupu na stránku = přesměrování, ztráta běžného práva = nic. |

---

## Rizika

| Riziko | Dopad | Ošetření |
|---|---|---|
| Neúplný seed při migraci | Vysoký | Dnešní ACL je `allow()` pro všechny s jedinou výjimkou. Po přechodu na *default deny* se aplikace zavře, pokud seed nepokryje každý klíč. Migrace musí projít registr a explicitně namapovat stávající chování. |
| Scoped práva u výpisů | Vysoký | Nelze filtrovat v PHP. Ve verzi 1.2 omezeno na operace nad jednou entitou. |
| Rozsah zásahu | Střední | Dotkne se každé fasády a většiny šablon. Fázovat — jádro zvlášť, prosazení do UI zvlášť. |
| Osiřelé klíče v databázi | Nízký | Po refaktoringu kódu zůstanou klíče, které registr nezná. Ignorovat při vyhodnocení, vypsat v adminu k úklidu. |
| Špatně zvolená sémantika priorit | Nízký | Evaluátor je čistá funkce nad sadou vstupů — testy podle [docs/testing.md](testing.md) sepsat dřív než UI, aby se sémantika dala měnit levně. |

---

## Fázování a stav implementace

### Fáze 1 — Jádro bez UI · hotovo

Po nasazení se navenek nic nezmění.

- [x] Migrace: `user_role`, `user_role_permission`, `user_x_user_role`, úprava `user`
- [x] Doménový balík rolí a oprávnění (entita, mapper, repozitář, service, fasáda)
- [x] `PermissionRegistry` a enumy definic po doménách
- [x] Evaluátor priorit + testy
- [x] Override `isAllowed()` v `SecurityUser`, odstranění `StaticAuthorizator`
- [x] Kontrola `admin.access` při přihlášení (`UserAuthenticator`)
- [x] Přiřazení rolí uživateli (multiselect v `UserForm`) — přesunuto z fáze 2, jinak by fáze 1 nebyla nasaditelná
- [x] Seed migrace zachovávající dnešní chování

### Fáze 2 — Admin rozhraní · hotovo

- [x] CRUD rolí včetně priority (`/admin/user-role`)
- [x] Třístavová matice oprávnění seskupená po entitách
- [x] Výpis osiřelých klíčů k úklidu
- [x] Guardy proti zamčení se ven navázané na UI

### Fáze 3 — Prosazení do aplikace · ~1–2 dny

Teprve tady se splní cíl „nezobrazovat podle role“.

- [ ] Atribut `#[RequiresPermission]` a kontrola `admin.access` v `BaseAdminPresenter`
- [ ] Vynucené odhlášení při ztrátě `admin.access`, `LogoutReason::AccessRevoked`
- [ ] Handling `InsufficientPrivilegesException` → 403
- [ ] Sidebar a šablony pod oprávnění
- [ ] Převod všech fasád na nové klíče

### Fáze 4 — Vlastnická práva · ~1–2 dny

Kandidát na odložení, pokud 1.2 tlačí termín — zbytek funguje i bez toho.

- [ ] `ScopeResolver` a klíče `.own`
- [ ] Rozšíření `isAllowed()` o parametr entity
- [ ] Latte funkce `isAllowed()` pro scoped kontroly

**Celkem ~6–10 dní**, bez fáze 4 zhruba 5–8.
