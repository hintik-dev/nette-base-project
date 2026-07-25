<?php declare(strict_types=1);
namespace App\Presentation\Modules\Web\Page;

use App\Domain\Page\Page;
use App\Model\Latte\BaseTemplate;

final class PageDefaultTemplate extends BaseTemplate
{
    public Page $page;

    public string $contentHtml;

    public string $browserTitle;

    /**
     * Slugy publikovaných stránek — @layout.latte s nimi ověřuje natvrdo
     * zapsané odkazy v navigaci, aby nevedly na smazanou/nepublikovanou stránku.
     *
     * @var array<int, string>
     */
    public array $publishedSlugs;
}
