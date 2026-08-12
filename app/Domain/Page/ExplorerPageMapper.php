<?php declare(strict_types=1);
namespace App\Domain\Page;

use Nette\Database\Table\ActiveRow;
use Nette\Utils\Json;

class ExplorerPageMapper
{
    public function mapPage(ActiveRow $row): Page
    {
        /** @var array<string, mixed> $content */
        $content = Json::decode((string) $row[ExplorerPageRepository::COLUMN_CONTENT], forceArrays: true);

        return new Page(
            id: $row[ExplorerPageRepository::COLUMN_ID],
            slug: $row[ExplorerPageRepository::COLUMN_SLUG],
            title: $row[ExplorerPageRepository::COLUMN_TITLE],
            authorId: $row[ExplorerPageRepository::COLUMN_AUTHOR_ID] !== null
                ? (int) $row[ExplorerPageRepository::COLUMN_AUTHOR_ID]
                : null,
            content: $content,
            status: PageStatus::from($row[ExplorerPageRepository::COLUMN_STATUS]),
            publishedAt: $row[ExplorerPageRepository::COLUMN_PUBLISHED_AT] ?? null,
        );
    }
}
