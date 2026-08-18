<?php declare(strict_types=1);
namespace App\Model\Security;

use Nette\Security\SimpleIdentity as NetteIdentity;

/**
 * Identita nenese role ani oprávnění — ta se vyhodnocují při každém requestu
 * z databáze, aby se jejich odebrání projevilo okamžitě.
 *
 * Příznak superadmina výjimkou není: DbUserStorage staví identitu znovu při
 * každém requestu z čerstvých dat, takže i on zůstává aktuální.
 */
class Identity extends NetteIdentity
{
    public const string DATA_EMAIL = 'email';

    public const string DATA_IS_SUPERADMIN = 'isSuperadmin';


    public function getEmail(): string
    {
        return (string) ($this->getData()[self::DATA_EMAIL] ?? '');
    }


    public function isSuperadmin(): bool
    {
        return (bool) ($this->getData()[self::DATA_IS_SUPERADMIN] ?? false);
    }
}
