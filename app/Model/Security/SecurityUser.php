<?php declare(strict_types=1);
namespace App\Model\Security;

use App\Domain\UserSession\LogoutReason;
use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\PermissionEvaluator;
use App\Model\Security\Permission\PermissionScope;
use App\Model\Security\Permission\ScopeResolverRegistry;
use App\Model\Security\Storage\DbUserStorage;
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

    private ScopeResolverRegistry $scopeResolverRegistry;


    /** Vstřikuje se přes setup v config/services.neon. */
    public function setPermissionEvaluator(PermissionEvaluator $permissionEvaluator): void
    {
        $this->permissionEvaluator = $permissionEvaluator;
    }


    /** Vstřikuje se přes setup v config/services.neon. */
    public function setScopeResolverRegistry(ScopeResolverRegistry $scopeResolverRegistry): void
    {
        $this->scopeResolverRegistry = $scopeResolverRegistry;
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


    /**
     * Kontrola oprávnění nad konkrétní entitou.
     *
     * Předává se **globální** varianta oprávnění; vlastnické (`.own`) se zkusí
     * automaticky, až když globální neprojde. Pořadí je podstatné — kdo smí
     * upravit libovolnou stránku, nepotřebuje být jejím autorem.
     *
     *     $user->isAllowedOn(PagePermission::Edit, $page)
     */
    public function isAllowedOn(PermissionDefinition $permission, object $entity): bool
    {
        if ($this->isAllowed($permission)) {
            return true;
        }

        $userId = $this->getUserId();

        if ($userId === null) {
            return false;
        }

        foreach (PermissionScope::cases() as $scope) {
            $scopedKey = $permission->getKey() . '.' . $scope->value;

            if (!$this->permissionEvaluator->isAllowed($userId, $scopedKey)) {
                continue;
            }

            if ($this->scopeResolverRegistry->matches($permission->getResource(), $scope, $entity, $userId)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Odhlásí uživatele se zaznamenaným důvodem. Nette\Security\User::logout()
     * žádný důvod nepředává, proto se nastavuje do storage předem.
     */
    public function forceLogout(LogoutReason $reason): void
    {
        $storage = $this->getStorage();

        if ($storage instanceof DbUserStorage) {
            $storage->setNextLogoutReason($reason);
        }

        $this->logout(clearIdentity: true);
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
