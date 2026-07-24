<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\Page;

use App\Domain\Page\Page;
use App\Model\Latte\BaseTemplate;

final class PageEditTemplate extends BaseTemplate
{
    public Page $page;

    public string $saveUrl;
}
