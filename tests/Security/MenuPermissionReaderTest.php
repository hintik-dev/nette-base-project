<?php declare(strict_types=1);
namespace Tests\Security;

require __DIR__ . '/../bootstrap.php';

use App\Domain\Page\PagePermission;
use App\Domain\UserRole\AclPermission;
use App\Presentation\Components\Admin\Menu\AdminMenuFactory;
use App\Presentation\Components\Admin\Menu\MenuItem;
use App\Presentation\Components\Admin\Menu\MenuPermissionReader;
use Nette\Application\PresenterFactory;
use Tester\Assert;
use Tester\TestCase;

/**
 * Menu odvozuje oprávnění z atributů na presenteru. Kdyby cíl v definici menu
 * neseděl na existující presenter a akci, spadl by celý admin až za běhu —
 * tenhle test to odchytí dřív.
 */
class MenuPermissionReaderTest extends TestCase
{
    /** Musí odpovídat sekci `application.mapping` v config/common.neon. */
    private const string ADMIN_MAPPING = 'App\Presentation\Modules\Admin\**Presenter';


    private function createReader(): MenuPermissionReader
    {
        $presenterFactory = new PresenterFactory();
        $presenterFactory->setMapping(['Admin' => self::ADMIN_MAPPING]);

        return new MenuPermissionReader($presenterFactory);
    }


    /**
     * Každý cíl v menu musí jít přeložit na existující presenter.
     */
    public function testEveryMenuDestinationResolves(): void
    {
        $reader = $this->createReader();

        foreach ($this->collectDestinations() as $destination) {
            Assert::noError(static function () use ($reader, $destination): void {
                $reader->getRequiredPermissions($destination);
            });
        }
    }


    /**
     * Položka bez jediného oprávnění by byla vidět úplně všem — to je skoro
     * vždy chyba v atributu na presenteru, ne záměr. Dashboard je výjimka,
     * ten má být dostupný každému, kdo se dostane do administrace.
     */
    public function testMenuItemsRequireSomePermission(): void
    {
        $reader = $this->createReader();

        foreach ($this->collectDestinations() as $destination) {
            if ($destination === ':Admin:Home:default') {
                continue;
            }

            Assert::notSame(
                [],
                $reader->getRequiredPermissions($destination),
                'Cíl ' . $destination . ' nevyžaduje žádné oprávnění.',
            );
        }
    }


    /**
     * Kontrola, že se opravdu čte atribut z presenteru, ne něco jiného.
     */
    public function testReadsPermissionFromPresenterAttribute(): void
    {
        $reader = $this->createReader();

        Assert::same(
            [[AclPermission::RoleList]],
            $reader->getRequiredPermissions(':Admin:UserRole:default'),
        );

        Assert::same(
            [[PagePermission::ListAll]],
            $reader->getRequiredPermissions(':Admin:Page:default'),
        );
    }


    /**
     * Atribut na třídě i na akci se skládají — obojí musí platit.
     */
    public function testClassAndActionAttributesCombine(): void
    {
        $reader = $this->createReader();

        Assert::same(
            [[PagePermission::ListAll], [PagePermission::Create]],
            $reader->getRequiredPermissions(':Admin:Page:create'),
        );
    }


    /** @return list<string> */
    private function collectDestinations(): array
    {
        $destinations = [];

        foreach ((new AdminMenuFactory())->create() as $section) {
            foreach ($section->items as $item) {
                $destinations = array_merge($destinations, $this->flatten($item));
            }
        }

        return $destinations;
    }


    /** @return list<string> */
    private function flatten(MenuItem $item): array
    {
        if (!$item->isGroup()) {
            return [(string) $item->destination];
        }

        $destinations = [];

        foreach ($item->children as $child) {
            $destinations = array_merge($destinations, $this->flatten($child));
        }

        return $destinations;
    }
}

(new MenuPermissionReaderTest())->run();
