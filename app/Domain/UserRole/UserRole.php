<?php declare(strict_types=1);
namespace App\Domain\UserRole;

enum UserRole: string
{
    case SUPERADMIN = 'superadmin';
    case ADMIN = 'admin';
    case USER = 'user';

    public function toLabel(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'Superadministrátor',
            self::ADMIN => 'Administrátor',
            self::USER => 'Uživatel',
        };
    }
}
