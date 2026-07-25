<?php declare(strict_types=1);
namespace App\Model\Latte;

use Nette\Neon\Neon;
use Nette\StaticClass;
use Nette\Utils\Json;

final class Filters
{
    use StaticClass;

    public static function neon(mixed $value): string
    {
        return Neon::encode($value, true);
    }

    public static function json(mixed $value): string
    {
        return Json::encode($value);
    }


    /**
     * Ochrana proti javascript:/data: URI ve vstupech od uživatele (např.
     * URL tlačítek v blocích CMS stránky) — relativní URL, kotvy a query
     * bez schématu projdou beze změny, jinak jen výslovně povolená schémata.
     */
    public static function safeUrl(mixed $value): string
    {
        $url = trim((string) $value);

        if ($url === '') {
            return '#';
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (!is_string($scheme) || in_array(strtolower($scheme), ['http', 'https', 'mailto', 'tel'], true)) {
            return $url;
        }

        return '#';
    }
}
