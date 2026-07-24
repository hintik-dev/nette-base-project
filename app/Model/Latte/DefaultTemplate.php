<?php declare(strict_types=1);
namespace App\Model\Latte;

/**
 * Výchozí šablona pro presentery a komponenty, které nepředávají žádná
 * vlastní typovaná data, a pro vendor komponenty (např. Contributte
 * Datagrid a jeho subkomponenty), které si do šablony dosazují proměnné
 * mimo naši kontrolu.
 *
 * Konkrétní presentery/komponenty s vlastními daty by měly dědit přímo
 * od {@see BaseTemplate} a deklarovat si typované vlastnosti, ne od této
 * třídy – ta dynamické vlastnosti záměrně povoluje.
 */
#[\AllowDynamicProperties]
final class DefaultTemplate extends BaseTemplate
{
}
