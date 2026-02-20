<?php

namespace App\Model\Security\Authorizator;

use Exception;

class InsufficientPrivilegesException extends Exception
{
    public function __construct()
    {
        parent::__construct('Insufficient privileges');
    }
}