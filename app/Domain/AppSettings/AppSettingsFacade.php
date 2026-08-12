<?php declare(strict_types=1);

namespace App\Domain\AppSettings;

use App\Domain\ValueStorage\ValueStorageService;
use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\SecurityUser;

class AppSettingsFacade
{
    private const string CATEGORY = 'app_settings';
    private const string KEY_SITE_NAME = 'site_name';
    private const string KEY_CONTACT_EMAIL = 'contact_email';


    public function __construct(
        private readonly ValueStorageService $valueStorageService,
        private readonly SecurityUser $securityUser,
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
        $this->assertAllowed(AppSettingsPermission::Edit);

        $this->valueStorageService->setMany(self::CATEGORY, [
            self::KEY_SITE_NAME => $siteName,
            self::KEY_CONTACT_EMAIL => $contactEmail,
        ]);
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    private function assertAllowed(PermissionDefinition $permission): void
    {
        if (!$this->securityUser->isAllowed($permission)) {
            throw new InsufficientPrivilegesException();
        }
    }
}
