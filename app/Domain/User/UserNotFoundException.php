<?php declare(strict_types=1);
namespace App\Domain\User;

use App\Model\Exception\EntityNotFoundException;
class UserNotFoundException extends EntityNotFoundException
{
    public function __construct()
    {
        parent::__construct('User not found.');
    }
}
