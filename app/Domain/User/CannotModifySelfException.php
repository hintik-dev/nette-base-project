<?php declare(strict_types=1);
namespace App\Domain\User;

use RuntimeException;

class CannotModifySelfException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Nelze deaktivovat vlastní účet.');
    }
}
