<?php declare(strict_types=1);
namespace App\Domain\Page;

use Nette\SmartObject;

class PageFormData
{
    use SmartObject;

    public const string PARAM_SLUG = 'slug';
    public const string PARAM_TITLE = 'title';
    public const string PARAM_IS_HOMEPAGE = 'isHomepage';

    public string $slug;

    public string $title;

    public bool $isHomepage = false;
}
