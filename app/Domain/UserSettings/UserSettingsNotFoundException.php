<?php declare(strict_types=1);
namespace App\Domain\UserSettings;

use App\Model\Exception\EntityNotFoundException;

class UserSettingsNotFoundException extends EntityNotFoundException
{
    public function __construct()
    {
        parent::__construct('User settings not found.');
    }
}
