<?php declare(strict_types=1);
namespace App\Domain\UserRole;

use DateTimeInterface;
use Nette\Security\Resource;

/**
 * Uživatelská role. Oprávnění se skládají vzestupně podle priority —
 * explicitní záznam ve vyšší prioritě přepíše nižší, neutrál nepřepisuje nic.
 *
 * Role s prioritou 0 je výchozí: aplikuje se všem uživatelům včetně těch bez
 * jakékoli role a nelze ji explicitně přiřadit.
 */
readonly class UserRole implements Resource
{
    public const string RESOURCE_ID = 'user-role';

    public const string DEFAULT_CODE = 'default';

    public const int DEFAULT_PRIORITY = 0;

    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public ?string $description,
        public int $priority,
        public bool $isSystem,
        public DateTimeInterface $createdAt,
    ) {
    }


    public function isDefault(): bool
    {
        return $this->priority === self::DEFAULT_PRIORITY;
    }


    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
