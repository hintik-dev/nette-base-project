<?php declare(strict_types=1);
namespace App\Model\Security\Permission;

/**
 * Rozhoduje, jestli konkrétní entita spadá do daného scope pro daného uživatele.
 *
 * Samotné ACL na to nestačí — `page.edit.own` říká „smí upravit vlastní stránku“,
 * ale co znamená „vlastní“ ví jen kód domény. Databáze proto drží jen přiřazení
 * klíče roli, pravidlo zůstává tady.
 */
interface ScopeResolver
{
    /** @param string $resource první segment klíče oprávnění, např. `page` */
    public function supports(string $resource, PermissionScope $scope): bool;

    public function matches(object $entity, int $userId): bool;
}
