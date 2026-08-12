<?php declare(strict_types=1);
namespace Tests\Security;

require __DIR__ . '/../bootstrap.php';

use App\Domain\Page\Page;
use App\Domain\Page\PageOwnerScopeResolver;
use App\Domain\Page\PagePermission;
use App\Domain\Page\PageStatus;
use App\Model\Security\Permission\PermissionScope;
use App\Model\Security\Permission\ScopeResolverRegistry;
use Tester\Assert;
use Tester\TestCase;

class ScopeResolverTest extends TestCase
{
    private const int AUTHOR_ID = 7;

    private const int STRANGER_ID = 8;


    public function testOwnerMatchesOwnPage(): void
    {
        $resolver = new PageOwnerScopeResolver();

        Assert::true($resolver->matches($this->createPage(self::AUTHOR_ID), self::AUTHOR_ID));
        Assert::false($resolver->matches($this->createPage(self::AUTHOR_ID), self::STRANGER_ID));
    }


    /**
     * Stránka bez autora nepatří nikomu — vlastnické právo na ni nesmí zabrat,
     * jinak by ji po migraci mohl upravit kdokoli s page.edit.own.
     */
    public function testPageWithoutAuthorBelongsToNobody(): void
    {
        $resolver = new PageOwnerScopeResolver();

        Assert::false($resolver->matches($this->createPage(null), self::AUTHOR_ID));
    }


    public function testResolverIgnoresOtherResources(): void
    {
        $resolver = new PageOwnerScopeResolver();

        Assert::true($resolver->supports(Page::RESOURCE_ID, PermissionScope::Own));
        Assert::false($resolver->supports('user', PermissionScope::Own));
    }


    public function testRegistryDelegatesToMatchingResolver(): void
    {
        $registry = new ScopeResolverRegistry([new PageOwnerScopeResolver()]);

        Assert::true($registry->matches(
            Page::RESOURCE_ID,
            PermissionScope::Own,
            $this->createPage(self::AUTHOR_ID),
            self::AUTHOR_ID,
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
            Page::RESOURCE_ID,
            PermissionScope::Own,
            $this->createPage(self::AUTHOR_ID),
            self::AUTHOR_ID,
        ));
    }


    /**
     * Klíč `page.edit.own` musí vracet scope Own a resource page — na tom
     * stojí dohledání resolveru v SecurityUser::isAllowedOn().
     */
    public function testScopedKeyIsParsedCorrectly(): void
    {
        Assert::same(PermissionScope::Own, PagePermission::EditOwn->getScope());
        Assert::same('page', PagePermission::EditOwn->getResource());

        Assert::null(PagePermission::Edit->getScope());
        Assert::same('page', PagePermission::Edit->getResource());
    }


    private function createPage(?int $authorId): Page
    {
        return new Page(
            id: 1,
            slug: 'test',
            title: 'Test',
            authorId: $authorId,
            content: [],
            status: PageStatus::Draft,
            publishedAt: null,
        );
    }
}

(new ScopeResolverTest())->run();
