<?php declare(strict_types=1);
namespace App\Domain\UserSession;

enum LogoutReason: string
{
    case Manual = 'manual';
    case Inactivity = 'inactivity';
    case Forced = 'forced';
    case SingleSession = 'single_session';
    case AccessRevoked = 'access_revoked';
    case PasswordReset = 'password_reset';
}
