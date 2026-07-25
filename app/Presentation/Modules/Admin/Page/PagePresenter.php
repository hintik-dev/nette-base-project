<?php declare(strict_types=1);

namespace App\Presentation\Modules\Admin\Page;

use App\Domain\Page\PageFacade;
use App\Presentation\Components\Admin\Page\PageForm\PageFormFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;
use Nette\Assets\EntryAsset;
use Nette\Assets\Registry as AssetsRegistry;
use Nette\Assets\StyleAsset;
use Nette\Utils\Json;

class PagePresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly PageFacade $pageFacade,
        private readonly PageFormFactory $pageFormFactory,
        private readonly AssetsRegistry $assetsRegistry,
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
        $template->webCssUrl = $this->getWebCssUrl();
    }


    /**
     * URL kompilovaného CSS veřejného webu — Puck editor si ho vstříkne do
     * vlastního izolovaného iframe canvasu, aby bloky při editaci vypadaly
     * stejně jako po publikaci (viz assets/admin/editor/main.tsx).
     */
    private function getWebCssUrl(): ?string
    {
        $webEntry = $this->assetsRegistry->tryGetAsset('web/main.js');

        if (!$webEntry instanceof EntryAsset) {
            return null;
        }

        foreach ($webEntry->imports as $import) {
            if ($import instanceof StyleAsset) {
                return $import->url;
            }
        }

        return null;
    }


    public function handleSave(int $id): void
    {
        /** @var array<string, mixed> $content */
        $content = Json::decode($this->getHttpRequest()->getRawBody() ?? '{}', forceArrays: true);

        $this->pageFacade->publishContent($id, $content);

        $this->sendJson(['ok' => true]);
    }
}
