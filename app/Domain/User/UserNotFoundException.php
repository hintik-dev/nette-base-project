<?php

namespace App\Domain\User;

use App\Core\Exception\EntityNotFoundException;

class UserNotFoundException extends EntityNotFoundException
{

    public function __construct()
    {
        parent::__construct('User not found.');
    }
}
