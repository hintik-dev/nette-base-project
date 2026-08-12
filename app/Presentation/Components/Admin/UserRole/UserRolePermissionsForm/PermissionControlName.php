<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\UserRole\UserRolePermissionsForm;

use LogicException;

/**
 * Převod klíče oprávnění na název formulářového prvku.
 *
 * Nette\ComponentModel\Container povoluje v názvu komponenty jen `[a-zA-Z0-9_]`,
 * kdežto klíče oprávnění mají tvar `entita.akce` s kebab-case segmenty —
 * `user.change-password`, `scheduled-job.run-now`. Tečka i pomlčka se proto
 * musí zakódovat.
 *
 * Tečka → `__`, pomlčka → `_`. Protože klíče samy podtržítko neobsahují,
 * je převod jednoznačný; buildMap() to navíc pro jistotu kontroluje, aby
 * případná budoucí kolize spadla hned a hlasitě.
 */
final class PermissionControlName
{
    public static function encode(string $permissionKey): string
    {
        return str_replace(['.', '-'], ['__', '_'], $permissionKey);
    }


    /**
     * @param list<string> $permissionKeys
     * @return array<string, string> klíč oprávnění => název prvku
     */
    public static function buildMap(array $permissionKeys): array
    {
        $map = [];

        foreach ($permissionKeys as $key) {
            $name = self::encode($key);

            $collision = array_search($name, $map, strict: true);

            if ($collision !== false) {
                throw new LogicException(sprintf(
                    'Oprávnění "%s" a "%s" se mapují na stejný název prvku "%s".',
                    $collision,
                    $key,
                    $name,
                ));
            }

            $map[$key] = $name;
        }

        return $map;
    }
}
