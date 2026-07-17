<?php declare(strict_types=1);
namespace App\Domain\UserRole;

enum UserRole: string
{
    case SuperAdmin = 'superadmin';
    case Admin = 'admin';
    case User = 'user';

    public function toLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Superadministrátor',
            self::Admin => 'Administrátor',
            self::User => 'Uživatel',
        };
    }
}
