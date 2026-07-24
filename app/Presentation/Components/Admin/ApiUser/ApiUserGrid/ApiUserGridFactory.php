<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\ApiUser\ApiUserGrid;

interface ApiUserGridFactory
{
    public function create(): ApiUserGrid;
}
