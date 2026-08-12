<?php declare(strict_types=1);
namespace App\Model\Security\Authorizator;

use Exception;

/**
 * Uživatel přišel o admin.access — nemá se kam přesměrovat, takže mu
 * nezbývá než vynucené odhlášení.
 */
class AccessRevokedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Access to administration has been revoked');
    }
}
