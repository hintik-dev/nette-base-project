<?php declare(strict_types=1);
namespace Tests\Security;

use App\Model\Security\Permission\PermissionScope;
use App\Model\Security\Permission\ScopeResolver;

final class OwnedThingScopeResolver implements ScopeResolver
{
    public function supports(string $resource, PermissionScope $scope): bool
    {
        if ($resource !== OwnedThing::RESOURCE_ID) {
            return false;
        }

        return match ($scope) {
            PermissionScope::Own => true,
        };
    }


    public function matches(object $entity, int $userId): bool
    {
        return $entity instanceof OwnedThing
            && $entity->ownerId !== null
            && $entity->ownerId === $userId;
    }
}
