<?php declare(strict_types=1);
namespace Tests\Security;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/SamplePermission.php';

use App\Domain\Page\PagePermission;
use App\Domain\UserRole\AclPermission;
use App\Model\Security\Permission\PermissionScope;
use Tester\Assert;
use Tester\TestCase;

class PermissionKeyParsingTest extends TestCase
{
    public function testScopedKeyIsParsed(): void
    {
        Assert::same(PermissionScope::Own, SamplePermission::EditOwn->getScope());
        Assert::same('sample', SamplePermission::EditOwn->getResource());
    }


    public function testGlobalKeyHasNoScope(): void
    {
        Assert::null(SamplePermission::Edit->getScope());
        Assert::same('sample', SamplePermission::Edit->getResource());
    }


    /**
     * Tříčlenný klíč, jehož poslední segment není scope, musí zůstat globální —
     * jinak by `acl.role.edit` vypadalo jako oprávnění vázané na vlastnictví.
     */
    public function testThreeSegmentKeyWithoutScopeStaysGlobal(): void
    {
        Assert::null(SamplePermission::ChangePassword->getScope());
        Assert::null(AclPermission::RoleEdit->getScope());
        Assert::same('acl', AclPermission::RoleEdit->getResource());
    }


    public function testRealPermissionsAreGlobal(): void
    {
        Assert::null(PagePermission::Edit->getScope());
        Assert::same('page', PagePermission::Edit->getResource());
    }
}

(new PermissionKeyParsingTest())->run();
