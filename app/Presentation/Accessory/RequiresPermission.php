<?php declare(strict_types=1);
namespace App\Presentation\Accessory;

use App\Model\Security\Permission\PermissionDefinition;
use Attribute;

/**
 * Oprávnění vyžadované pro vstup na presenter nebo akci.
 *
 * Vyhodnocuje ho BaseAdminPresenter::checkRequirements(), takže se kontrola
 * opakuje při každém requestu — uživatel, kterému bylo právo odebráno, na
 * stránce nezůstane déle než do dalšího kliknutí. Zároveň to znamená, že se
 * ACL nedá obejít přímým vstupem na URL.
 *
 * Atribut na třídě platí pro všechny akce, atribut na akci ho zpřesňuje.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class RequiresPermission
{
    public function __construct(
        public PermissionDefinition $permission,
    ) {
    }
}
