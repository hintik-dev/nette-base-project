# Formuláře

Formuláře v projektu jsou implementovány jako **součást komponent** (viz [Komponenty](components.md)). Každý formulář je Nette Form vložený do komponenty, která se pak registruje v presenteru.

---

## Základní třída BaseForm

Všechny formuláře dědí od `App\Presentation\Control\Form\BaseForm`, která rozšiřuje `Nette\Forms\Form`:

```php
// app/Presentation/Control/Form/BaseForm.php
class BaseForm extends Nette\Forms\Form
{
    // společná nastavení pro všechny formuláře
}
```

---

## Vytvoření formuláře v komponentě

Formulář se vytváří v metodě `createComponentForm()` uvnitř komponenty. Komponenta dědí od `BaseComponent`.

### Příklad: jednoduchý formulář

```php
<?php declare(strict_types=1);

namespace App\Presentation\Components\Web\Contact\ContactForm;

use App\Domain\Contact\ContactFacade;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;

class ContactForm extends BaseComponent
{
    public function __construct(
        private readonly ContactFacade $contactFacade,
    ) {
    }

    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();

        $form->addText('name', 'Jméno')
            ->setRequired();

        $form->addEmail('email', 'E-mail')
            ->setRequired();

        $form->addTextArea('message', 'Zpráva')
            ->setRequired()
            ->addRule($form::MinLength, null, 10);

        $form->addSubmit('submit', 'Odeslat');

        $form->onSuccess[] = fn(BaseForm $form, ContactFormData $values) => $this->saveForm($form, $values);

        return $form;
    }

    private function saveForm(BaseForm $form, ContactFormData $formData): void
    {
        $this->contactFacade->sendContact($formData);
        $this->presenter->flashMessage('Zpráva byla odeslána', 'success');
        $this->presenter->redirect('this');
    }
}
```

---

## Data Transfer Object (DTO) pro formulář

Pro typově bezpečné zpracování hodnot formuláře se používá DTO třída. Nette automaticky mapuje hodnoty formuláře na tento objekt.

```php
<?php declare(strict_types=1);

namespace App\Domain\Contact;

class ContactFormData
{
    public string $name;
    public string $email;
    public string $message;
}
```

DTO je pak použito jako parametr `onSuccess` callbacku — Nette provede automatické mapování.

---

## Validace

Nette Forms poskytuje bohatou sadu validačních pravidel:

```php
$form->addText('username', 'Uživatelské jméno')
    ->setRequired()
    ->addRule($form::MinLength, null, 3)
    ->addRule($form::MaxLength, null, 50)
    ->addRule($form::Pattern, 'Pouze písmena a čísla', '[a-zA-Z0-9]+');

$form->addEmail('email', 'E-mail')
    ->setRequired();

$form->addPassword('password', 'Heslo')
    ->setRequired()
    ->addRule($form::MinLength, null, 8);

$form->addInteger('age', 'Věk')
    ->addRule($form::Range, null, [18, 120]);
```

Chybové zprávy pro validaci jsou lokalizovány v `config/forms.neon`.

---

## Interface Factory

Ke každé komponentě s formulářem patří **interface factory**, která umožňuje Nette DI automaticky vytvořit instanci komponenty:

```php
<?php declare(strict_types=1);

namespace App\Presentation\Components\Web\Contact\ContactForm;

interface ContactFormFactory
{
    public function create(): ContactForm;
}
```

Factory **není potřeba implementovat** — Nette DI ji vygeneruje automaticky.

---

## Registrace factory v konfiguraci

Factory rozhraní se registruje v `config/services.neon`. Třídy odpovídající vzoru `*Factory` jsou registrovány automaticky díky sekci `search`:

```neon
search:
    - in: %appDir%
      classes:
          - *Factory
```

Není tedy nutná žádná ruční registrace.

---

## Použití formuláře v presenteru

V presenteru se komponenta (a s ní i formulář) registruje přes `createComponent*` metodu:

```php
class HomePresenter extends BaseWebPresenter
{
    public function __construct(
        private readonly ContactFormFactory $contactFormFactory,
    ) {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        $this->addComponent(
            $this->contactFormFactory->create(),
            'contactForm',
        );
    }

    // NEBO alternativně jako createComponent* metoda:
    public function createComponentContactForm(): ContactForm
    {
        return $this->contactFormFactory->create();
    }
}
```

Doporučený způsob je volání `$this->addComponent()` v `action*` metodě, protože umožňuje předat do komponenty parametry závislé na aktuální akci (např. ID záznamu).

---

## Vykreslení formuláře v šabloně

V Latte šabloně presenteru:

```latte
{* Celá komponenta (formulář) *}
{control contactForm}
```

V šabloně samotné komponenty (`ContactForm.latte`) pak vykreslíte jednotlivé části formuláře:

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

    <div>
        <label n:name="message">Zpráva:</label>
        <textarea n:name="message"></textarea>
        {inputError message}
    </div>

    <button n:name="submit">Odeslat</button>
</form>
```

