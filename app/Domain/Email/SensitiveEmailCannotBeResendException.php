<?php declare(strict_types=1);

namespace App\Domain\Email;

use Exception;

class SensitiveEmailCannotBeResendException extends Exception
{
    public function __construct(int $id)
    {
        parent::__construct(sprintf('E-mail #%d obsahuje citlivá data a nelze jej znovu odeslat z historie.', $id));
    }
}
