<?php declare(strict_types=1);
namespace App\Domain\Page;

use App\Model\Exception\EntityNotFoundException;

class PageNotFoundException extends EntityNotFoundException
{
    public function __construct()
    {
        parent::__construct('Page not found.');
    }
}
