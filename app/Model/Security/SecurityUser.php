<?php declare(strict_types = 1);

namespace App\Model\Security;

use Nette\Security\User as NetteUser;

/**
 * @method Identity getIdentity()
 */
final class SecurityUser extends NetteUser
{

}
