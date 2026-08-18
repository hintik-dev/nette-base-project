<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Menu;

use App\Model\Security\Permission\PermissionDefinition;
use App\Presentation\Accessory\RequiresPermission;
use Nette\Application\IPresenterFactory;
use ReflectionClass;
use ReflectionMethod;

/**
 * Čte oprávnění vyžadovaná cílovým presenterem z atributů `#[RequiresPermission]`.
 *
 * Dřív bylo právo zapsané dvakrát — v šabloně menu (co se zobrazí) a v atributu
 * (co se vynutí) — a nic nehlídalo, že se ty zápisy shodují. Zdrojem pravdy je
 * proto kód, který přístup skutečně vynucuje; menu se jen ptá.
 *
 * Reflexe běží jednou na cíl a request, výsledek se drží v paměti. Instance
 * presenteru nevzniká, jen se autoloadne třída.
 *
 * Registruje se ručně v config/services.neon — název neodpovídá vzorům v `search`.
 */
final class MenuPermissionReader
{
    /** @var array<string, list<list<PermissionDefinition>>> */
    private array $cache = [];


    public function __construct(
        private readonly IPresenterFactory $presenterFactory,
    ) {
    }


    /**
     * Vrací skupiny oprávnění: uvnitř skupiny stačí jedno (OR), mezi skupinami
     * musí projít všechny (AND) — stejná sémantika jako u atributu.
     *
     * @return list<list<PermissionDefinition>>
     */
    public function getRequiredPermissions(string $destination): array
    {
        if (isset($this->cache[$destination])) {
            return $this->cache[$destination];
        }

        [$presenterName, $action] = self::splitDestination($destination);

        // Neexistující cíl je chyba v definici menu, ne stav k potichu skrytí —
        // getPresenterClass() proto necháme vyhodit výjimku.
        $reflection = new ReflectionClass($this->presenterFactory->getPresenterClass($presenterName));

        $groups = self::readAttributes($reflection);

        foreach (['action', 'render'] as $prefix) {
            $method = $prefix . ucfirst($action);

            if ($reflection->hasMethod($method)) {
                $groups = array_merge($groups, self::readAttributes($reflection->getMethod($method)));
            }
        }

        return $this->cache[$destination] = $groups;
    }


    /**
     * @template T of object
     * @param ReflectionClass<T>|ReflectionMethod $element
     * @return list<list<PermissionDefinition>>
     */
    private static function readAttributes(ReflectionClass|ReflectionMethod $element): array
    {
        $groups = [];

        foreach ($element->getAttributes(RequiresPermission::class) as $attribute) {
            $groups[] = $attribute->newInstance()->permissions;
        }

        return $groups;
    }


    /**
     * `:Admin:Page:default` => ['Admin:Page', 'default']
     *
     * @return array{string, string}
     */
    private static function splitDestination(string $destination): array
    {
        $parts = explode(':', trim($destination, ':'));
        $action = array_pop($parts);

        return [implode(':', $parts), $action === '' ? 'default' : $action];
    }
}
