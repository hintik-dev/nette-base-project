<?php declare(strict_types=1);
namespace App\Model\Security;

use App\Domain\User\User;

class IdentityFactory
{
    public function createIdentity(User $user): Identity
    {
        // Role se do identity záměrně nepředávají — o oprávněních rozhoduje
        // PermissionEvaluator nad aktuálním stavem databáze.
        return new Identity($user->id, null, [
            Identity::DATA_EMAIL => $user->email,
            Identity::DATA_IS_SUPERADMIN => $user->isSuperadmin,
        ]);
    }
}
