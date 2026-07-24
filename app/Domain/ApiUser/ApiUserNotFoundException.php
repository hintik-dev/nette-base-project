<?php declare(strict_types=1);
namespace App\Domain\ApiUser;

use App\Model\Exception\EntityNotFoundException;

class ApiUserNotFoundException extends EntityNotFoundException
{
    public function __construct()
    {
        parent::__construct('ApiUser');
    }
}
