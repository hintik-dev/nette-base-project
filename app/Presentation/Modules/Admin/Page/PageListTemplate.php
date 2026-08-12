<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\Page;

use App\Model\Latte\BaseTemplate;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

final class PageListTemplate extends BaseTemplate
{
    /** @var Selection<ActiveRow> */
    public Selection $pages;

    /** @var list<int> */
    public array $editablePageIds = [];
}
