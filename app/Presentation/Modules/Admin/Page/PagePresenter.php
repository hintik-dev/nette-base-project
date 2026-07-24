<?php declare(strict_types=1);

namespace App\Presentation\Modules\Admin\Page;

use App\Domain\Page\PageFacade;
use App\Presentation\Components\Admin\Page\PageForm\PageFormFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;
use Nette\Utils\Json;

class PagePresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly PageFacade $pageFacade,
        private readonly PageFormFactory $pageFormFactory,
    ) {
        parent::__construct();
    }


    public function actionDefault(): void
    {
        /** @var PageListTemplate $template */
        $template = $this->template;
        $template->pages = $this->pageFacade->getAllPagesDataSource();
    }


    public function actionCreate(): void
    {
        $this->addComponent($this->pageFormFactory->create(), 'pageForm');
    }


    public function actionEdit(int $id): void
    {
        /** @var PageEditTemplate $template */
        $template = $this->template;
        $template->page = $this->pageFacade->getPageById($id);
        $template->saveUrl = $this->link('save!', ['id' => $id]);
    }


    public function handleSave(int $id): void
    {
        /** @var array<string, mixed> $content */
        $content = Json::decode($this->getHttpRequest()->getRawBody() ?? '{}', forceArrays: true);

        $this->pageFacade->publishContent($id, $content);

        $this->sendJson(['ok' => true]);
    }
}
