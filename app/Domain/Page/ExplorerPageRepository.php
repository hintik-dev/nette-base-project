<?php declare(strict_types=1);
namespace App\Domain\Page;

use App\Core\Database\ExplorerRepository;
use DateTimeInterface;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\Json;
use RuntimeException;
use stdClass;

class ExplorerPageRepository extends ExplorerRepository
{
    public const string COLUMN_ID = 'id';
    public const string COLUMN_SLUG = 'slug';
    public const string COLUMN_TITLE = 'title';
    public const string COLUMN_CONTENT = 'content';
    public const string COLUMN_STATUS = 'status';
    public const string COLUMN_PUBLISHED_AT = 'published_at';

    public const string TABLE_NAME = 'page';


    public function __construct(
        private readonly ExplorerPageMapper $pageMapper,
    ) {
        parent::__construct(self::TABLE_NAME);
    }


    /** @return Selection<ActiveRow> */
    public function getAllDataSource(): Selection
    {
        return $this->findAll();
    }


    /** @return array<int, string> */
    public function getPublishedSlugs(): array
    {
        return $this->getTable()
            ->where(self::COLUMN_STATUS, PageStatus::Published->value)
            ->fetchPairs(null, self::COLUMN_SLUG);
    }


    /**
     * @throws PageNotFoundException
     */
    public function getPageById(int $id): Page
    {
        $pageRow = $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->fetch();

        if ($pageRow === null) {
            throw new PageNotFoundException();
        }

        return $this->pageMapper->mapPage($pageRow);
    }


    /**
     * @throws PageNotFoundException
     */
    public function getPageBySlug(string $slug): Page
    {
        $pageRow = $this->getTable()
            ->where(self::COLUMN_SLUG, $slug)
            ->fetch();

        if ($pageRow === null) {
            throw new PageNotFoundException();
        }

        return $this->pageMapper->mapPage($pageRow);
    }


    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $selection = $this->getTable()
            ->where(self::COLUMN_SLUG, $slug);

        if ($excludeId !== null) {
            $selection->where(self::COLUMN_ID . ' != ?', $excludeId);
        }

        return $selection->count() > 0;
    }


    public function createPage(string $slug, string $title): Page
    {
        $row = $this->getTable()->insert([
            self::COLUMN_SLUG => $slug,
            self::COLUMN_TITLE => $title,
            self::COLUMN_CONTENT => Json::encode(['content' => [], 'root' => ['props' => new stdClass()]]),
            self::COLUMN_STATUS => PageStatus::Draft->value,
        ]);

        if (!($row instanceof ActiveRow)) {
            throw new RuntimeException('Failed to create page');
        }

        return $this->pageMapper->mapPage($row);
    }


    /**
     * @param array<string, mixed> $content
     */
    public function publishContent(int $id, array $content, DateTimeInterface $publishedAt): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([
                self::COLUMN_CONTENT => Json::encode($content),
                self::COLUMN_STATUS => PageStatus::Published->value,
                self::COLUMN_PUBLISHED_AT => $publishedAt,
            ]);
    }
}
