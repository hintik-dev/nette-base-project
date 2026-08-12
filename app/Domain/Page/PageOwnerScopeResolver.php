<?php declare(strict_types=1);
namespace App\Domain\Page;

use App\Model\Security\Permission\PermissionScope;
use App\Model\Security\Permission\ScopeResolver;

/**
 * „Vlastní stránka“ znamená stránka, u které je uživatel veden jako autor.
 */
final class PageOwnerScopeResolver implements ScopeResolver
{
    public function supports(string $resource, PermissionScope $scope): bool
    {
        if ($resource !== Page::RESOURCE_ID) {
            return false;
        }

        // Match záměrně místo porovnání — přibude-li další scope, PHPStan
        // upozorní, že se tady musí rozhodnout, jestli ho stránky podporují.
        return match ($scope) {
            PermissionScope::Own => true,
        };
    }


    public function matches(object $entity, int $userId): bool
    {
        return $entity instanceof Page && $entity->isAuthoredBy($userId);
    }
}
