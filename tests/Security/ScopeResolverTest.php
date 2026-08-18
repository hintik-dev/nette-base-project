<?php declare(strict_types=1);
namespace Tests\Security;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/OwnedThing.php';
require __DIR__ . '/OwnedThingScopeResolver.php';

use App\Model\Security\Permission\PermissionScope;
use App\Model\Security\Permission\ScopeResolverRegistry;
use Tester\Assert;
use Tester\TestCase;

class ScopeResolverTest extends TestCase
{
    private const int OWNER_ID = 7;

    private const int STRANGER_ID = 8;


    public function testResolverMatchesOwnEntity(): void
    {
        $resolver = new OwnedThingScopeResolver();

        Assert::true($resolver->matches(new OwnedThing(self::OWNER_ID), self::OWNER_ID));
        Assert::false($resolver->matches(new OwnedThing(self::OWNER_ID), self::STRANGER_ID));
    }


    /**
     * Entita bez vlastníka nepatří nikomu. Kdyby to bylo obráceně, po zavedení
     * vlastnictví u existujících dat by na ně dosáhl každý s právem `.own`.
     */
    public function testEntityWithoutOwnerBelongsToNobody(): void
    {
        Assert::false((new OwnedThingScopeResolver())->matches(new OwnedThing(null), self::OWNER_ID));
    }


    public function testResolverIgnoresOtherResources(): void
    {
        $resolver = new OwnedThingScopeResolver();

        Assert::true($resolver->supports(OwnedThing::RESOURCE_ID, PermissionScope::Own));
        Assert::false($resolver->supports('page', PermissionScope::Own));
    }


    public function testRegistryDelegatesToMatchingResolver(): void
    {
        $registry = new ScopeResolverRegistry([new OwnedThingScopeResolver()]);

        Assert::true($registry->matches(
            OwnedThing::RESOURCE_ID,
            PermissionScope::Own,
            new OwnedThing(self::OWNER_ID),
            self::OWNER_ID,
        ));
    }


    /**
     * Chybějící resolver musí znamenat zákaz. Kdyby registry vracela true,
     * stačilo by přidat klíč `.own` bez implementace a vzniklo by tiché povolení.
     */
    public function testRegistryDeniesWhenNoResolverHandlesScope(): void
    {
        $registry = new ScopeResolverRegistry([]);

        Assert::false($registry->matches(
            OwnedThing::RESOURCE_ID,
            PermissionScope::Own,
            new OwnedThing(self::OWNER_ID),
            self::OWNER_ID,
        ));
    }
}

(new ScopeResolverTest())->run();
