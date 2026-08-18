<?php declare(strict_types=1);
namespace App\Model\Security\Permission;

/**
 * Sdružuje všechny ScopeResolvery. Registruje se ručně v config/services.neon,
 * kde se jí předá seznam přes typed().
 */
final class ScopeResolverRegistry
{
    /** @param list<ScopeResolver> $resolvers */
    public function __construct(
        private readonly array $resolvers,
    ) {
    }


    /**
     * Když scope nikdo neumí vyhodnotit, vrací false. Chybějící resolver tedy
     * znamená zákaz, ne tiché povolení.
     */
    public function matches(string $resource, PermissionScope $scope, object $entity, int $userId): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($resource, $scope)) {
                return $resolver->matches($entity, $userId);
            }
        }

        return false;
    }
}
