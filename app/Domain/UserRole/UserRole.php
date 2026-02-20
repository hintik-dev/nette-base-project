<?php

namespace App\Domain\UserRole;

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    public function toLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrátor',
            self::USER => 'Uživatel',
        };
    }
}
