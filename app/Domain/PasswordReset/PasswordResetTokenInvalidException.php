<?php declare(strict_types=1);

namespace App\Domain\PasswordReset;

use Exception;

class PasswordResetTokenInvalidException extends Exception
{
    public function __construct()
    {
        parent::__construct('Odkaz pro obnovu hesla je neplatný, expirovaný nebo už byl použit.');
    }
}
