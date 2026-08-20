<?php declare(strict_types=1);
namespace App\Presentation\Control\Form\DynamicSelect;

use App\Model\Security\Permission\PermissionDefinition;

/**
 * Zdroj dat pro DynamicSelect/DynamicMultiSelect v AJAX režimu. Registruje se
 * pod klíčem v DynamicSelectSourceRegistry (config/services.neon) a jeho
 * search() se volá z DynamicSelectPresenter — jednoho sdíleného endpointu
 * pro všechny dynamické selecty v aplikaci.
 */
interface DynamicSelectSource
{
    /** Klíč, pod kterým je zdroj dostupný přes DynamicSelectPresenter (?source=). */
    public function getKey(): string;

    /**
     * Oprávnění potřebné k prohledávání tohoto zdroje, nebo null když stačí
     * běžný přístup do administrace (DynamicSelectPresenter ho ověřuje sám —
     * RequiresPermission atribut je statický a nejde navázat na dynamický
     * parametr source).
     */
    public function getPermission(): ?PermissionDefinition;

    /**
     * @param int<0, max> $limit
     * @return list<array{value: string, label: string}>
     */
    public function search(string $query, int $limit): array;

    /**
     * Popisky pro už vybrané hodnoty — používá se při vykreslení formuláře,
     * aby nebylo nutné načítat celý seznam jen kvůli výchozí hodnotě.
     *
     * @param list<string> $values
     * @return array<int|string, string>
     */
    public function resolveLabels(array $values): array;
}
