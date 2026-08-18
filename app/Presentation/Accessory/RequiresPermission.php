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
 * Uvnitř jednoho atributu platí OR, mezi opakovanými atributy AND:
 *
 *     #[RequiresPermission(FooPermission::Edit, FooPermission::EditOwn)]
 *
 * Dvojice globálního a vlastnického oprávnění je hrubá branka na vstupu —
 * pustí dál i toho, kdo smí upravovat jen vlastní záznamy. Které konkrétní
 * to jsou, rozhodne až fasáda přes isAllowedOn(), protože tam je po ruce
 * entita; v checkRequirements() ještě není.
 *
 * Atribut na třídě platí pro všechny akce, atribut na akci ho zpřesňuje.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class RequiresPermission
{
    /** @var list<PermissionDefinition> */
    public array $permissions;


    public function __construct(PermissionDefinition ...$permissions)
    {
        $this->permissions = array_values($permissions);
    }
}
