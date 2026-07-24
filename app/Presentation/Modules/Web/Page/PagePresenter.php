<?php declare(strict_types=1);

namespace App\Presentation\Modules\Web\Page;

use App\Domain\Page\BlockRenderer;
use App\Domain\Page\PageFacade;
use App\Domain\Page\PageNotFoundException;
use App\Presentation\Modules\Web\BaseWebPresenter;
use Nette\Application\BadRequestException;

class PagePresenter extends BaseWebPresenter
{
    public function __construct(
        private readonly PageFacade $pageFacade,
        private readonly BlockRenderer $blockRenderer,
    ) {
        parent::__construct();
    }


    public function actionDefault(string $slug): void
    {
        try {
            $page = $this->pageFacade->getPublishedPageBySlug($slug);
        } catch (PageNotFoundException $e) {
            throw new BadRequestException('Page not found.', 404, $e);
        }

        /** @var PageDefaultTemplate $template */
        $template = $this->template;
        $template->page = $page;
        $template->contentHtml = $this->blockRenderer->render($page->content['content'] ?? []);
    }
}
