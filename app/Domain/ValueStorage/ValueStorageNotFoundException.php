<?php declare(strict_types=1);
namespace App\Domain\ValueStorage;

use App\Model\Exception\EntityNotFoundException;

class ValueStorageNotFoundException extends EntityNotFoundException
{
    public function __construct()
    {
        parent::__construct('Value storage entry not found.');
    }
}
