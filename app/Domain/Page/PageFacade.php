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
        private readonly ExplorerPageMapper $pageMapper,
        private readonly SecurityUser $securityUser,
    ) {
    }


    /**
     * @throws InsufficientPrivilegesException
     * @return Selection<ActiveRow>
     */
    public function getAllPagesDataSource(): Selection
    {
        if (!$this->securityUser->isAllowed(PagePermission::ListAll)) {
            throw new InsufficientPrivilegesException();
        }

        return $this->pageRepository->getAllDataSource();
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    public function getPageById(int $id): Page
    {
        $page = $this->pageService->getPageById($id);

        // Načtení musí předcházet kontrole — vlastnické právo se nedá
        // vyhodnotit bez entity, na kterou se ptáme.
        if (!$this->securityUser->isAllowedOn(PagePermission::Edit, $page)) {
            throw new InsufficientPrivilegesException();
        }

        return $page;
    }


    /**
     * Pro šablony a gridy — nehází výjimku, jen odpoví, jestli uživatel
     * na tu konkrétní stránku dosáhne.
     */
    public function canEdit(Page $page): bool
    {
        return $this->securityUser->isAllowedOn(PagePermission::Edit, $page);
    }


    /**
     * ID stránek z výpisu, na které uživatel dosáhne. Počítá se jedním
     * průchodem už načtenými řádky, aby výpis nedělal dotaz na řádek —
     * a hlavně aby pravidlo vlastnictví zůstalo tady, ne v šabloně.
     *
     * @param Selection<ActiveRow> $pages
     * @return list<int>
     */
    public function getEditablePageIds(Selection $pages): array
    {
        $ids = [];

        foreach ($pages as $row) {
            $page = $this->pageMapper->mapPage($row);

            if ($this->securityUser->isAllowedOn(PagePermission::Edit, $page)) {
                $ids[] = $page->id;
            }
        }

        return $ids;
    }


    /**
     * Veřejná metoda — bez kontroly přihlášení, vrací i draft (rozlišení dělá volající).
     */
    public function getPageBySlug(string $slug): Page
    {
        return $this->pageService->getPageBySlug($slug);
    }


    /**
     * Veřejná metoda — bez kontroly přihlášení, používá se jen k ověření
     * existence stránky (viz @layout.latte a Web\Page\PagePresenter), ne
     * k výpisu obsahu.
     *
     * @return array<int, string>
     */
    public function getPublishedSlugs(): array
    {
        return $this->pageService->getPublishedSlugs();
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
        if (!$this->securityUser->isAllowed(PagePermission::Create)) {
            throw new InsufficientPrivilegesException();
        }

        $slug = $data->isHomepage ? '' : $data->slug;

        return $this->pageService->createPage($slug, $data->title, $this->securityUser->getUserId());
    }


    /**
     * @param array<string, mixed> $content
     * @throws InsufficientPrivilegesException
     */
    public function publishContent(int $id, array $content): void
    {
        $page = $this->pageService->getPageById($id);

        if (
            !$this->securityUser->isAllowedOn(PagePermission::Edit, $page)
            || !$this->securityUser->isAllowed(PagePermission::Publish)
        ) {
            throw new InsufficientPrivilegesException();
        }

        $this->pageService->publishContent($id, $content);
    }


    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        return $this->pageService->slugExists($slug, $excludeId);
    }
}
