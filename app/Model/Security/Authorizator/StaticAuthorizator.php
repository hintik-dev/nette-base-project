<?php declare(strict_types=1);
namespace App\Model\Security\Authorizator;

use App\Domain\Page\Page;
use App\Domain\User\User;
use App\Domain\UserRole\UserRole;
use Nette\Security\Permission;

final class StaticAuthorizator extends Permission
{
    /**
     * Create ACL
     */
    public function __construct()
    {
        $this->addRoles();
        $this->addResources();
        $this->addPermissions();
    }

    /**ser
     * Setup roles
     */
    protected function addRoles(): void
    {
        $this->addRole('guest');
        $this->addRole(UserRole::User->value, 'guest');
        $this->addRole(UserRole::Admin->value, 'user');
        $this->addRole(UserRole::SuperAdmin->value, 'admin');
    }

    /**
     * Setup resources
     */
    protected function addResources(): void
    {
        $this->addResource(User::RESOURCE_ID);
        $this->addResource(Page::RESOURCE_ID);
        $this->addResource('value-storage');
    }

    /**
     * Setup ACL
     */
    protected function addPermissions(): void
    {
        // Zatím všem povolíme vše
        $this->allow();
    }
}
