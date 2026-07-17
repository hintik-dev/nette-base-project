<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\ApiUser\ApiUserForm;

use App\Domain\ApiUser\ApiUserFacade;
use App\Domain\ApiUser\ApiUserFormData;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;

class ApiUserForm extends BaseComponent
{
    public function __construct(
        private readonly ApiUserFacade $apiUserFacade,
        private readonly ?int $editId,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();

        $form->addText(ApiUserFormData::PARAM_NAME, 'Název')
            ->setRequired('Zadejte název API uživatele.')
            ->setMaxLength(100);

        $form->addTextArea(ApiUserFormData::PARAM_DESCRIPTION, 'Popis')
            ->setMaxLength(500);

        $form->addCheckbox(ApiUserFormData::PARAM_IS_ACTIVE, 'Aktivní');

        $form->addText(ApiUserFormData::PARAM_VALID_FROM, 'Platný od')
            ->setHtmlType('datetime-local');

        $form->addText(ApiUserFormData::PARAM_VALID_TO, 'Platný do')
            ->setHtmlType('datetime-local');

        $form->addSubmit('submit', $this->editId !== null ? 'Uložit změny' : 'Vytvořit API uživatele');

        if ($this->editId !== null) {
            $apiUser = $this->apiUserFacade->getById($this->editId);
            $form->setDefaults([
                ApiUserFormData::PARAM_NAME        => $apiUser->name,
                ApiUserFormData::PARAM_DESCRIPTION => $apiUser->description ?? '',
                ApiUserFormData::PARAM_IS_ACTIVE   => $apiUser->isActive,
                ApiUserFormData::PARAM_VALID_FROM  => $apiUser->validFrom?->format('Y-m-d\TH:i'),
                ApiUserFormData::PARAM_VALID_TO    => $apiUser->validTo?->format('Y-m-d\TH:i'),
            ]);
        } else {
            $form->setDefaults([ApiUserFormData::PARAM_IS_ACTIVE => true]);
        }

        $form->onSuccess[] = fn(BaseForm $form, ApiUserFormData $data) => $this->saveForm($data);

        return $form;
    }


    public function render(mixed $params = null): void
    {
        $this->getTemplate()->editId = $this->editId;
        parent::render($params);
    }


    private function saveForm(ApiUserFormData $data): void
    {
        if ($this->editId !== null) {
            $this->apiUserFacade->update($this->editId, $data);
            $this->flashSuccess('API uživatel byl uložen.');
            $this->presenter->redirect('edit', ['id' => $this->editId]);
        } else {
            $newId = $this->apiUserFacade->create($data);
            $this->flashSuccess('API uživatel byl vytvořen. Zkopírujte si API token.');
            $this->presenter->redirect('edit', ['id' => $newId]);
        }
    }
}
