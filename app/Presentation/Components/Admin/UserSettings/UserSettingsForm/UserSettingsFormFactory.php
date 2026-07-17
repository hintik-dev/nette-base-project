<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserSettings\UserSettingsForm;

interface UserSettingsFormFactory
{
    public function create(): UserSettingsForm;
}
