<?php declare(strict_types=1);
namespace App\Domain\UserSettings;

use Nette\SmartObject;

class UserSettingsFormData
{
    use SmartObject;

    public const string PARAM_THEME = 'theme';

    public string $theme = AppearanceTheme::Light->value;
}
