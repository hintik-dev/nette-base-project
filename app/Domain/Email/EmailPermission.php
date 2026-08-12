<?php declare(strict_types=1);
namespace App\Domain\Email;

use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\Permission\TPermissionDefinition;

enum EmailPermission: string implements PermissionDefinition
{
    use TPermissionDefinition;

    case ListAll = 'email.list';
    case Detail = 'email.detail';
    case Send = 'email.send';
    case Resend = 'email.resend';


    public function getLabel(): string
    {
        return match ($this) {
            self::ListAll => 'Zobrazit odeslané e-maily',
            self::Detail  => 'Zobrazit detail e-mailu',
            self::Send    => 'Odeslat e-mail',
            self::Resend  => 'Odeslat e-mail znovu',
        };
    }


    public function getGroup(): string
    {
        return 'E-maily';
    }
}
