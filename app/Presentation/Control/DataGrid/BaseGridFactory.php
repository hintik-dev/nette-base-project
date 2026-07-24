<?php declare(strict_types=1);

namespace App\Presentation\Control\DataGrid;

interface BaseGridFactory
{
    public function create(): BaseGrid;
}
