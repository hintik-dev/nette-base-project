<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserRole\UserRolePermissionsForm;

use App\Model\Security\Permission\PermissionScope;

/**
 * Jeden řádek matice oprávnění — spojuje definici z registru s názvem
 * formulářového prvku, pod kterým se v šabloně vykresluje.
 */
readonly class PermissionMatrixRow
{
    public function __construct(
        public string $key,
        public string $label,
        public string $controlName,
        public ?PermissionScope $scope,
    ) {
    }
}
