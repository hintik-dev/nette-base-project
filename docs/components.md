# Komponenty

Komponenty jsou **znovupoužitelné UI prvky**, které zapouzdřují logiku, šablonu a případně i formulář. Každá komponenta se skládá ze tří souborů.

---

## Struktura komponenty

Každá komponenta se skládá ze tří souborů ve společném adresáři:

```
app/Presentation/Components/{Modul}/{Sekce}/{NázevKomponenty}/
├── {NázevKomponenty}.php           # Hlavní třída komponenty
├── {NázevKomponenty}.latte         # Latte šablona
└── {NázevKomponenty}Factory.php    # Interface factory
```

**Příklad — přihlašovací formulář:**

```
app/Presentation/Components/Admin/Sign/SignInForm/
├── SignInForm.php
├── SignInForm.latte
└── SignInFormFactory.php
```

---

## 1. Hlavní třída komponenty

Třída musí dědit od `App\Presentation\Components\Base\BaseComponent`:

```php
<?php declare(strict_types=1);

namespace App\Presentation\Components\Web\Article\ArticleList;

use App\Domain\Article\ArticleFacade;
use App\Presentation\Components\Base\BaseComponent;

class ArticleList extends BaseComponent
{
    /** @var array<int, Article> */
    private array $articles = [];

    public function __construct(
        private readonly ArticleFacade $articleFacade,
        private readonly int $limit = 10,
    ) {
    }

    public function render(): void
    {
        $this->articles = $this->articleFacade->getLatestArticles($this->limit);

        // Předání dat do šablony
        $template = $this->getTemplate();
        $template->articles = $this->articles;

        parent::render();
    }
}
```

`BaseComponent` automaticky:
- Najde šablonu `{NázevTřídy}.latte` ve stejném adresáři jako PHP soubor
- Předá proměnnou `$componentName` do šablony

---

## 2. Latte šablona

Šablona leží ve stejném adresáři jako PHP třída a má **shodný název**:

```latte
{* ArticleList.latte *}
<div class="article-list">
    {foreach $articles as $article}
        <article>
            <h2>{$article->title}</h2>
            <p>{$article->perex}</p>
            <a n:href="Web:Article:detail $article->id">Číst více</a>
        </article>
    {else}
        <p>Žádné články nebyly nalezeny.</p>
    {/foreach}
</div>
```

---

## 3. Interface Factory

Factory rozhraní umožňuje Nette DI automaticky vytvářet instance komponenty:

```php
<?php declare(strict_types=1);

namespace App\Presentation\Components\Web\Article\ArticleList;

interface ArticleListFactory
{
    public function create(int $limit = 10): ArticleList;
}
```

**Pravidla pro factory:**
- Je to **pouze interface** — implementaci vytvoří Nette DI automaticky
- Metoda se musí jmenovat `create()`
- Parametry metody `create()` se předají konstruktoru komponenty (ostatní závislosti DI doplní samo)

---

## Registrace komponenty

Factory rozhraní jsou automaticky registrována díky sekci `search` v `config/services.neon`:

```neon
search:
    - in: %appDir%
      classes:
          - *Factory
```

**Není potřeba žádná ruční registrace** — stačí vytvořit soubor a pojmenovat interface `{Název}Factory`.

---

## Použití komponenty v presenteru

### Doporučený způsob — přes addComponent v action metodě

Komponenty se přidávají v `action*` metodě presenteru, protože tato metoda se volá před renderováním a umožňuje předat parametry (např. ID záznamu):

```php
class ArticlePresenter extends BaseWebPresenter
{
    public function __construct(
        private readonly ArticleListFactory $articleListFactory,
    ) {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        $this->addComponent(
            $this->articleListFactory->create(limit: 5),
            'articleList',
        );
    }
}
```

### Alternativní způsob — přes createComponent metodu

Pro jednoduché komponenty bez parametrů závislých na akci:

```php
public function createComponentArticleList(): ArticleList
{
    return $this->articleListFactory->create();
}
```

---

## Vykreslení komponenty v šabloně

V Latte šabloně presenteru vykreslíte komponentu pomocí `{control}`:

```latte
{* Vykreslení celé komponenty *}
{control articleList}
```

---

## Komponenta s formulářem

Pokud komponenta obsahuje formulář, vytváří se formulář jako subkomponenta přes metodu `createComponentForm()`:

```php
class ContactForm extends BaseComponent
{
    public function __construct(
        private readonly ContactFacade $contactFacade,
    ) {
    }

    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();
        $form->addText('name', 'Jméno')->setRequired();
        $form->addEmail('email', 'E-mail')->setRequired();
        $form->addSubmit('submit', 'Odeslat');

        $form->onSuccess[] = fn(BaseForm $form, ContactFormData $values)
            => $this->saveForm($form, $values);

        return $form;
    }

    private function saveForm(BaseForm $form, ContactFormData $formData): void
    {
        $this->contactFacade->sendContact($formData);
        $this->presenter->flashMessage('Zpráva odeslána', 'success');
        $this->presenter->redirect('this');
    }
}
```

V šabloně komponenty (`ContactForm.latte`) pak vykreslíte formulář:

```latte
<form n:name="form">
    <div>
        <label n:name="name">Jméno:</label>
        <input n:name="name">
        {inputError name}
    </div>
    <div>
        <label n:name="email">E-mail:</label>
        <input n:name="email">
        {inputError email}
    </div>
    <button n:name="submit">Odeslat</button>
</form>
```

---

## BaseComponent — jak funguje

```php
class BaseComponent extends Control
{
    protected ?string $latteFile = null;

    public function render(mixed $params = null): void
    {
        // Pokud není explicitně nastaven soubor šablony,
        // automaticky najde {NázevTřídy}.latte ve stejném adresáři
        if (empty($this->latteFile)) {
            $this->latteFile = $this->getComponentNameWithPath();
        }

        $this->getTemplate()->setFile($this->latteFile . '.latte');
        $this->getTemplate()->componentName = $this->getComponentName();
        $this->getTemplate()->render();
    }
}
```

Pokud potřebujete použít jiný soubor šablony, nastavte `$this->latteFile` v komponentě:

```php
public function render(): void
{
    $this->latteFile = __DIR__ . '/CustomTemplate';
    parent::render();
}
```

---

## Celkové schéma

```
Presenter
  └─ action*() / createComponent*()
        └─ {Název}Factory->create(...)      ← DI vytvoří instanci
              └─ {Název} extends BaseComponent
                    ├─ render()              ← příprava dat + šablona
                    ├─ createComponentForm() ← formulář (volitelný)
                    └─ {Název}.latte         ← šablona
```
