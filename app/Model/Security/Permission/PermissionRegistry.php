<?php declare(strict_types=1);
namespace App\Model\Security\Permission;

use App\Domain\ApiUser\ApiUserPermission;
use App\Domain\AppSettings\AppSettingsPermission;
use App\Domain\Email\EmailPermission;
use App\Domain\Notification\NotificationPermission;
use App\Domain\Page\PagePermission;
use App\Domain\ScheduledJob\ScheduledJobPermission;
use App\Domain\User\UserPermission;
use App\Domain\UserRole\AclPermission;
use App\Domain\UserSession\UserSessionPermission;
use App\Domain\ValueStorage\ValueStoragePermission;

/**
 * Soupis všech oprávnění, která aplikace zná.
 *
 * Definice samotné patří k doménám; tady je jen seznam enumů, které se mají
 * načíst — přidání domény je tedy jeden řádek. Registr je zdrojem pravdy:
 * klíč, který v něm není, nemá kdo kontrolovat, takže se při vyhodnocení
 * ignoruje (v databázi po refaktoringu zůstane jako osiřelý záznam).
 *
 * Registruje se ručně v config/services.neon — název neodpovídá žádnému
 * ze vzorů v sekci `search`.
 */
final class PermissionRegistry
{
    /** @var list<class-string<PermissionDefinition>> */
    private const array DEFINITION_ENUMS = [
        AdminPermission::class,
        AclPermission::class,
        UserPermission::class,
        UserSessionPermission::class,
        PagePermission::class,
        EmailPermission::class,
        NotificationPermission::class,
        ScheduledJobPermission::class,
        ApiUserPermission::class,
        AppSettingsPermission::class,
        ValueStoragePermission::class,
    ];

    /** @var array<string, PermissionDefinition>|null */
    private ?array $byKey = null;


    /**
     * Všechna oprávnění indexovaná klíčem, v pořadí deklarace.
     *
     * @return array<string, PermissionDefinition>
     */
    public function getAll(): array
    {
        if ($this->byKey !== null) {
            return $this->byKey;
        }

        $byKey = [];

        foreach (self::DEFINITION_ENUMS as $enumClass) {
            foreach ($enumClass::cases() as $definition) {
                $byKey[$definition->getKey()] = $definition;
            }
        }

        return $this->byKey = $byKey;
    }


    /**
     * Oprávnění seskupená pro matici v administraci.
     *
     * @return array<string, list<PermissionDefinition>>
     */
    public function getGrouped(): array
    {
        $grouped = [];

        foreach ($this->getAll() as $definition) {
            $grouped[$definition->getGroup()][] = $definition;
        }

        return $grouped;
    }


    public function find(string $key): ?PermissionDefinition
    {
        return $this->getAll()[$key] ?? null;
    }


    public function has(string $key): bool
    {
        return isset($this->getAll()[$key]);
    }


    /**
     * Klíče uložené v databázi, které registr nezná — zbytky po refaktoringu.
     *
     * @param list<string> $storedKeys
     * @return list<string>
     */
    public function findOrphanKeys(array $storedKeys): array
    {
        $all = $this->getAll();

        return array_values(array_filter(
            $storedKeys,
            static fn (string $key): bool => !isset($all[$key]),
        ));
    }
}
