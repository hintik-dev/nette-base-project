<?php declare(strict_types=1);
namespace Tests\Security;

require __DIR__ . '/../bootstrap.php';

use App\Model\Security\Permission\PermissionRegistry;
use App\Presentation\Components\Admin\UserRole\UserRolePermissionsForm\PermissionControlName;
use LogicException;
use Tester\Assert;
use Tester\TestCase;

/**
 * Matice oprávnění dělá z každého klíče formulářový prvek. Nette dovolí
 * v názvu komponenty jen `[a-zA-Z0-9_]`, takže klíč s tečkou nebo pomlčkou
 * shodí celou stránku až za běhu — tenhle test to odchytí dřív, a to i pro
 * oprávnění, která teprve přibudou.
 */
class PermissionControlNameTest extends TestCase
{
    /** Stejná podmínka jako Nette\ComponentModel\Container::NameRegexp. */
    private const string NETTE_NAME_REGEXP = '#^[a-zA-Z0-9_]+$#D';


    public function testEveryRegisteredKeyProducesValidComponentName(): void
    {
        foreach (array_keys((new PermissionRegistry())->getAll()) as $key) {
            $controlName = PermissionControlName::encode($key);

            // Záměrně preg_match a ne Assert::match — Tester nebere vzor
            // s modifikátorem D jako regulární výraz a porovnal by ho doslova.
            Assert::same(
                1,
                preg_match(self::NETTE_NAME_REGEXP, $controlName),
                'Oprávnění "' . $key . '" se mapuje na neplatný název komponenty "' . $controlName . '".',
            );
        }
    }


    public function testRegisteredKeysDoNotCollide(): void
    {
        $keys = array_keys((new PermissionRegistry())->getAll());

        $map = PermissionControlName::buildMap($keys);

        Assert::same(count($keys), count(array_unique($map)));
    }


    public function testEncodesDotsAndHyphens(): void
    {
        Assert::same('user__change_password', PermissionControlName::encode('user.change-password'));
        Assert::same('scheduled_job__run_now', PermissionControlName::encode('scheduled-job.run-now'));
        Assert::same('page__edit', PermissionControlName::encode('page.edit'));
    }


    public function testCollisionIsReportedLoudly(): void
    {
        Assert::exception(
            static fn () => PermissionControlName::buildMap(['a.b', 'a--b']),
            LogicException::class,
        );
    }
}

(new PermissionControlNameTest())->run();
