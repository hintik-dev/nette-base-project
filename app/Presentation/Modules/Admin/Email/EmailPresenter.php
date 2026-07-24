<?php declare(strict_types=1);

namespace App\Presentation\Modules\Admin\Email;

use App\Domain\Email\SentEmailFacade;
use App\Presentation\Components\Admin\Email\ComposeEmailForm\ComposeEmailFormFactory;
use App\Presentation\Components\Admin\Email\SentEmailGrid\SentEmailGridFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;

class EmailPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly SentEmailGridFactory $gridFactory,
        private readonly ComposeEmailFormFactory $composeFormFactory,
        private readonly SentEmailFacade $facade,
    ) {
        parent::__construct();
    }


    public function actionList(): void
    {
        $this->addComponent($this->gridFactory->create(), 'sentEmailGrid');
    }


    public function actionCompose(): void
    {
        $this->addComponent($this->composeFormFactory->create(), 'composeEmailForm');
    }


    public function handleResend(int $id): void
    {
        try {
            $this->facade->resend($id);
            $this->flashSuccess('E-mail byl znovu odeslán.');
        } catch (\Throwable $e) {
            $this->flashError('Opětovné odeslání selhalo: ' . $e->getMessage());
        }

        $this->redirect('this');
    }


    public function actionDetail(int $id): void
    {
        /** @var EmailDetailTemplate $template */
        $template = $this->template;
        $template->email = $this->facade->getById($id);
    }
}
