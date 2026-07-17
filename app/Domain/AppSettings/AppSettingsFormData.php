<?php declare(strict_types=1);

namespace App\Domain\AppSettings;

use Nette\SmartObject;

class AppSettingsFormData
{
    use SmartObject;

    public ?string $siteName = null;
    public ?string $contactEmail = null;
}
