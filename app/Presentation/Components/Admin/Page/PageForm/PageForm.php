<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Page\PageForm;

use App\Domain\Page\PageFacade;
use App\Domain\Page\PageFormData;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;
use Nette\Forms\Control;

/**
 * @property-read PageFormTemplate $template
 */
class PageForm extends BaseComponent
{
    public function __construct(
        private readonly PageFacade $pageFacade,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();

        $slug = $form->addText(PageFormData::PARAM_SLUG, 'Slug');

        $isHomepage = $form->addCheckbox(PageFormData::PARAM_IS_HOMEPAGE, 'Nastavit jako úvodní stránku')
            ->addRule(
                fn(Control $control): bool => !$control->getValue() || !$this->pageFacade->slugExists(''),
                'Úvodní stránka už existuje — nejdřív jí musíte změnit slug.',
            );

        // Úvodní stránka má vždy prázdný slug (odpovídá kořenovému '/'),
        // pole Slug proto dává smysl vyžadovat jen když se nezaškrtne.
        $slug->addConditionOn($isHomepage, $form::Equal, false)
            ->setRequired('Zadejte slug.')
            ->addRule($form::Pattern, 'Slug smí obsahovat jen malá písmena, číslice a pomlčky.', '[a-z0-9\-]+')
            ->addRule(
                fn(Control $control): bool => !$this->pageFacade->slugExists((string) $control->getValue()),
                'Stránka s tímto slugem již existuje.',
            );

        $form->addText(PageFormData::PARAM_TITLE, 'Název')
            ->setRequired('Zadejte název.');

        $form->addSubmit('submit', 'Vytvořit stránku');

        $form->onSuccess[] = fn(BaseForm $form, PageFormData $data) => $this->saveForm($data);

        return $form;
    }


    public function render(mixed $params = null): void
    {
        $this->template->backLink = $this->presenter->link('default');
        parent::render($params);
    }


    private function saveForm(PageFormData $data): void
    {
        $page = $this->pageFacade->create($data);
        $this->flashSuccess('Stránka byla vytvořena.');
        $this->presenter->redirect('edit', ['id' => $page->id]);
    }
}
