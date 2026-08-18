<?php declare(strict_types=1);
namespace App\Domain\UserRole;

use Exception;

/**
 * Kód i priorita jsou unikátní — priorita proto, aby při skládání oprávnění
 * nikdy nevznikla dvojice rolí se stejnou vahou a nejednoznačným výsledkem.
 */
class UserRoleConflictException extends Exception
{
}
