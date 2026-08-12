<?php declare(strict_types=1);
namespace App\Model\Security;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\PermissionEvaluator;
use Nette\Security\User as NetteUser;
use Override;

/**
 * Nette\Security\User::isAllowed() iteruje role a vrací true u první povolující —
 * je to OR přes role po jedné, takže se autorizátor nikdy nedozví celou sadu.
 * Tím by zanikla jak třístavovost (deny by nepřebil allow z jiné role), tak
 * skládání podle priority. Proto se metoda přepisuje a rozhodnutí dělá
 * PermissionEvaluator nad celou sadou rolí najednou.
 *
 * @method Identity getIdentity()
 */
final class SecurityUser extends NetteUser
{
    private PermissionEvaluator $permissionEvaluator;


    /** Vstřikuje se přes setup v config/services.neon. */
    public function setPermissionEvaluator(PermissionEvaluator $permissionEvaluator): void
    {
        $this->permissionEvaluator = $permissionEvaluator;
    }


    /**
     * Přijímá buď definici oprávnění, nebo dvojici resource + akce:
     *
     *     $user->isAllowed(PagePermission::Edit)
     *     $user->isAllowed('page', 'edit')
     *
     * @param PermissionDefinition|string|null $resource
     * @param string|null $privilege
     */
    #[Override]
    public function isAllowed(mixed $resource = null, mixed $privilege = null): bool
    {
        if ($this->isSuperadmin()) {
            return true;
        }

        $key = self::buildPermissionKey($resource, $privilege);

        if ($key === null) {
            return false;
        }

        return $this->permissionEvaluator->isAllowed($this->getUserId(), $key);
    }


    /**
     * Superadmin obchází ACL úplně — je to pojistka proti zamčení se ven
     * ze správy oprávnění, ne zkratka pro běžná práva.
     */
    public function isSuperadmin(): bool
    {
        $identity = $this->isLoggedIn() ? parent::getIdentity() : null;

        return $identity instanceof Identity && $identity->isSuperadmin();
    }


    /** Vrací null pro nepřihlášeného — pak platí jen oprávnění výchozí role. */
    public function getUserId(): ?int
    {
        return $this->isLoggedIn() ? (int) $this->getId() : null;
    }


    private static function buildPermissionKey(mixed $resource, mixed $privilege): ?string
    {
        if ($resource instanceof PermissionDefinition) {
            return $resource->getKey();
        }

        if (!is_string($resource) || $resource === '') {
            return null;
        }

        return is_string($privilege) && $privilege !== ''
            ? $resource . '.' . $privilege
            : $resource;
    }
}
