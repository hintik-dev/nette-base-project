<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Menu;

use App\Model\Security\SecurityUser;

/**
 * Odpovídá, jestli aktuální uživatel dosáhne na daný cíl menu.
 *
 * Co daný cíl vyžaduje, zjišťuje MenuPermissionReader z atributů na presenteru —
 * tady se to jen porovná s právy uživatele.
 *
 * Registruje se ručně v config/services.neon.
 */
final class MenuPermissionResolver
{
    public function __construct(
        private readonly MenuPermissionReader $reader,
        private readonly SecurityUser $securityUser,
    ) {
    }


    public function isAllowed(string $destination): bool
    {
        foreach ($this->reader->getRequiredPermissions($destination) as $alternatives) {
            foreach ($alternatives as $permission) {
                if ($this->securityUser->isAllowed($permission)) {
                    continue 2;
                }
            }

            return false;
        }

        return true;
    }
}
