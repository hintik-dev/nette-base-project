<?php declare(strict_types=1);
namespace App\Model\Security;

use App\Domain\User\User;

class IdentityFactory
{
    public function createIdentity(User $user): Identity
    {
        return new Identity($user->id, [$user->role->value], [
            'email' => $user->email,
        ]);
    }
}
