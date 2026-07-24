<?php declare(strict_types=1);
namespace App\Domain\Page;

use DateTimeImmutable;

readonly class PageService
{
    public function __construct(
        private ExplorerPageRepository $pageRepository,
    ) {
    }


    public function getPageById(int $id): Page
    {
        return $this->pageRepository->getPageById($id);
    }


    public function getPageBySlug(string $slug): Page
    {
        return $this->pageRepository->getPageBySlug($slug);
    }


    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        return $this->pageRepository->slugExists($slug, $excludeId);
    }


    public function createPage(string $slug, string $title): Page
    {
        return $this->pageRepository->createPage($slug, $title);
    }


    /**
     * @param array<string, mixed> $content
     */
    public function publishContent(int $id, array $content): void
    {
        $this->pageRepository->publishContent($id, $content, new DateTimeImmutable());
    }
}
