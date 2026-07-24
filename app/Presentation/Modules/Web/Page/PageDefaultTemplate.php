<?php declare(strict_types=1);
namespace App\Presentation\Modules\Web\Page;

use App\Domain\Page\Page;
use App\Model\Latte\BaseTemplate;

final class PageDefaultTemplate extends BaseTemplate
{
    public Page $page;

    public string $contentHtml;
}
