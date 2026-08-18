<?php declare(strict_types=1);
namespace App\Domain\UserRole;

use Exception;

/**
 * Výchozí roli nelze smazat, přiřadit ani jí změnit prioritu.
 */
class DefaultRoleException extends Exception
{
}
