<?php declare(strict_types=1);
namespace App\Domain\Page;

use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\SecurityUser;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class PageFacade
{
    public function __construct(
        private readonly PageService $pageService,
        private readonly ExplorerPageRepository $pageRepository,
        private readonly SecurityUser $securityUser,
    ) {
    }


    /**
     * @throws InsufficientPrivilegesException
     * @return Selection<ActiveRow>
     */
    public function getAllPagesDataSource(): Selection
    {
        if (!$this->securityUser->isAllowed(Page::RESOURCE_ID, 'list')) {
            throw new InsufficientPrivilegesException();
        }

        return $this->pageRepository->getAllDataSource();
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    public function getPageById(int $id): Page
    {
        if (!$this->securityUser->isAllowed(Page::RESOURCE_ID, 'edit')) {
            throw new InsufficientPrivilegesException();
        }

        return $this->pageService->getPageById($id);
    }


    /**
     * Veřejná metoda — bez kontroly přihlášení, vrací i draft (rozlišení dělá volající).
     */
    public function getPageBySlug(string $slug): Page
    {
        return $this->pageService->getPageBySlug($slug);
    }


    /**
     * @throws PageNotFoundException Pokud stránka neexistuje nebo není publikovaná.
     */
    public function getPublishedPageBySlug(string $slug): Page
    {
        $page = $this->pageService->getPageBySlug($slug);

        if ($page->status !== PageStatus::Published) {
            throw new PageNotFoundException();
        }

        return $page;
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    public function create(PageFormData $data): Page
    {
        if (!$this->securityUser->isAllowed(Page::RESOURCE_ID, 'create')) {
            throw new InsufficientPrivilegesException();
        }

        return $this->pageService->createPage($data->slug, $data->title);
    }


    /**
     * @param array<string, mixed> $content
     * @throws InsufficientPrivilegesException
     */
    public function publishContent(int $id, array $content): void
    {
        if (!$this->securityUser->isAllowed(Page::RESOURCE_ID, 'publish')) {
            throw new InsufficientPrivilegesException();
        }

        $this->pageService->publishContent($id, $content);
    }


    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        return $this->pageService->slugExists($slug, $excludeId);
    }
}
