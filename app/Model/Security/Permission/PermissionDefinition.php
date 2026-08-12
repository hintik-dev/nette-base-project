<?php declare(strict_types=1);
namespace App\Model\Security\Permission;

use BackedEnum;

/**
 * Definice jednoho oprávnění.
 *
 * Implementují ji enumy v doménových balících (např. App\Domain\Page\PagePermission),
 * PermissionRegistry je sbírá dohromady. Zdrojem pravdy o existujících oprávněních
 * je tedy kód — databáze drží jen přiřazení rolím jako textový klíč.
 *
 * Klíč má tvar `entita.akce`, případně `entita.akce.scope` u oprávnění vázaných
 * na vlastnost entity (viz PermissionScope).
 */
interface PermissionDefinition extends BackedEnum
{
    /** Klíč ukládaný do user_role_permission.permission_key */
    public function getKey(): string;

    /** První segment klíče — entita, ke které se oprávnění vztahuje */
    public function getResource(): string;

    /** Popisek v matici oprávnění */
    public function getLabel(): string;

    /** Skupina v matici oprávnění — typicky název entity */
    public function getGroup(): string;

    /** Vazba na vlastnost entity; null = globální oprávnění */
    public function getScope(): ?PermissionScope;
}
