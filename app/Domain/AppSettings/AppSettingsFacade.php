<?php declare(strict_types=1);

namespace App\Domain\AppSettings;

use App\Domain\ValueStorage\ValueStorageService;

class AppSettingsFacade
{
    private const string CATEGORY = 'app_settings';
    private const string KEY_SITE_NAME = 'site_name';
    private const string KEY_CONTACT_EMAIL = 'contact_email';


    public function __construct(
        private readonly ValueStorageService $valueStorageService,
    ) {
    }


    public function getSiteName(): string
    {
        return $this->valueStorageService->get(self::CATEGORY, self::KEY_SITE_NAME) ?? '';
    }


    public function getContactEmail(): string
    {
        return $this->valueStorageService->get(self::CATEGORY, self::KEY_CONTACT_EMAIL) ?? '';
    }


    public function saveGeneralSettings(string $siteName, string $contactEmail): void
    {
        $this->valueStorageService->setMany(self::CATEGORY, [
            self::KEY_SITE_NAME => $siteName,
            self::KEY_CONTACT_EMAIL => $contactEmail,
        ]);
    }
}
