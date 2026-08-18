<?php declare(strict_types=1);
namespace Tests\Security;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/FakeUserRoleRepository.php';

use App\Model\Security\Permission\PermissionEvaluator;
use Tester\Assert;
use Tester\TestCase;

class PermissionEvaluatorTest extends TestCase
{
    /**
     * Klíč, o kterém nikdo nerozhodl, je zakázaný.
     */
    public function testDefaultDeny(): void
    {
        $evaluator = new PermissionEvaluator(new FakeUserRoleRepository());

        Assert::false($evaluator->isAllowed(1, 'page.edit'));
    }


    public function testDefaultRoleGrants(): void
    {
        $evaluator = new PermissionEvaluator(new FakeUserRoleRepository([
            ['priority' => 0, 'permission_key' => 'page.edit', 'effect' => 'allow'],
        ]));

        Assert::true($evaluator->isAllowed(1, 'page.edit'));
    }


    /**
     * Deny z vyšší priority přebije allow z nižší.
     */
    public function testHigherPriorityDenyOverridesLowerAllow(): void
    {
        $evaluator = new PermissionEvaluator(new FakeUserRoleRepository([
            ['priority' => 0, 'permission_key' => 'page.delete', 'effect' => 'allow'],
            ['priority' => 20, 'permission_key' => 'page.delete', 'effect' => 'deny'],
        ]));

        Assert::false($evaluator->isAllowed(1, 'page.delete'));
    }


    /**
     * A stejně tak allow z vyšší priority přebije deny z nižší — právě proto
     * priorita existuje. Model „deny vyhrává vždy“ by tenhle případ nezvládl.
     */
    public function testHigherPriorityAllowOverridesLowerDeny(): void
    {
        $evaluator = new PermissionEvaluator(new FakeUserRoleRepository([
            ['priority' => 0, 'permission_key' => 'page.delete', 'effect' => 'deny'],
            ['priority' => 20, 'permission_key' => 'page.delete', 'effect' => 'allow'],
        ]));

        Assert::true($evaluator->isAllowed(1, 'page.delete'));
    }


    /**
     * Neutrál se v databázi neukládá, takže vyšší role bez záznamu
     * nemá jak přepsat allow z nižší.
     */
    public function testNeutralDoesNotOverride(): void
    {
        $evaluator = new PermissionEvaluator(new FakeUserRoleRepository([
            ['priority' => 10, 'permission_key' => 'page.edit', 'effect' => 'allow'],
            ['priority' => 20, 'permission_key' => 'user.list', 'effect' => 'allow'],
        ]));

        Assert::true($evaluator->isAllowed(1, 'page.edit'));
        Assert::true($evaluator->isAllowed(1, 'user.list'));
    }


    /**
     * Skládá se přes víc rolí i klíčů najednou.
     */
    public function testCompositionAcrossRoles(): void
    {
        $evaluator = new PermissionEvaluator(new FakeUserRoleRepository([
            ['priority' => 0, 'permission_key' => 'page.delete', 'effect' => 'deny'],
            ['priority' => 10, 'permission_key' => 'page.edit', 'effect' => 'allow'],
            ['priority' => 10, 'permission_key' => 'page.delete', 'effect' => 'allow'],
            ['priority' => 20, 'permission_key' => 'page.delete', 'effect' => 'deny'],
        ]));

        Assert::true($evaluator->isAllowed(1, 'page.edit'));
        Assert::false($evaluator->isAllowed(1, 'page.delete'));
        Assert::false($evaluator->isAllowed(1, 'user.list'));
    }


    /**
     * Nepřihlášenému platí jen výchozí role.
     */
    public function testAnonymousUsesDefaultRoleOnly(): void
    {
        $repository = new FakeUserRoleRepository(
            [['priority' => 10, 'permission_key' => 'page.edit', 'effect' => 'allow']],
            [['priority' => 0, 'permission_key' => 'page.list', 'effect' => 'allow']],
        );

        $evaluator = new PermissionEvaluator($repository);

        Assert::true($evaluator->isAllowed(null, 'page.list'));
        Assert::false($evaluator->isAllowed(null, 'page.edit'));
    }


    /**
     * Cache platí v rámci requestu — jinak by každá kontrola znamenala dotaz.
     */
    public function testCachesWithinRequest(): void
    {
        $repository = new FakeUserRoleRepository([
            ['priority' => 0, 'permission_key' => 'page.edit', 'effect' => 'allow'],
        ]);

        $evaluator = new PermissionEvaluator($repository);

        $evaluator->isAllowed(1, 'page.edit');
        $evaluator->isAllowed(1, 'page.list');
        $evaluator->isAllowed(1, 'user.list');

        Assert::same(1, $repository->queryCount);
    }


    /**
     * Po změně rolí se cache zahodí, aby se zásah projevil ještě v témže requestu.
     */
    public function testClearCacheForcesReload(): void
    {
        $repository = new FakeUserRoleRepository([
            ['priority' => 0, 'permission_key' => 'page.edit', 'effect' => 'allow'],
        ]);

        $evaluator = new PermissionEvaluator($repository);

        $evaluator->isAllowed(1, 'page.edit');
        $evaluator->clearCache();
        $evaluator->isAllowed(1, 'page.edit');

        Assert::same(2, $repository->queryCount);
    }


    /**
     * Různí uživatelé se cachují odděleně.
     */
    public function testCacheIsPerUser(): void
    {
        $repository = new FakeUserRoleRepository([
            ['priority' => 0, 'permission_key' => 'page.edit', 'effect' => 'allow'],
        ]);

        $evaluator = new PermissionEvaluator($repository);

        $evaluator->isAllowed(1, 'page.edit');
        $evaluator->isAllowed(2, 'page.edit');

        Assert::same(2, $repository->queryCount);
    }
}

(new PermissionEvaluatorTest())->run();
