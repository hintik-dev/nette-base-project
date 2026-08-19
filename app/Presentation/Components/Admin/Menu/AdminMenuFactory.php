<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Menu;

/**
 * Jediné místo, kde se definuje struktura administrátorského menu.
 *
 * Oprávnění se tady záměrně nezapisuje — odvodí se z atributu
 * `#[RequiresPermission]` na cílovém presenteru. Nová položka tedy znamená
 * jeden řádek tady a atribut na presenteru, nikdy podmínku v šabloně.
 */
class AdminMenuFactory
{
    /** @return list<MenuSection> */
    public function create(): array
    {
        return [
            new MenuSection(null, [
                new MenuItem(
                    label: 'Dashboard',
                    destination: ':Admin:Home:default',
                    icon: 'house-fill',
                    activePattern: ':Admin:Home:default',
                ),
            ]),

            new MenuSection('CMS', [
                new MenuItem(
                    label: 'Stránky',
                    destination: ':Admin:Page:default',
                    icon: 'file-earmark-richtext-fill',
                    activePattern: ':Admin:Page:*',
                ),
            ]),

            new MenuSection('Správa', [
                new MenuItem(
                    label: 'Uživatelé',
                    destination: ':Admin:User:list',
                    icon: 'people-fill',
                    activePattern: ':Admin:User:*',
                ),
                new MenuItem(
                    label: 'Role a oprávnění',
                    destination: ':Admin:UserRole:default',
                    icon: 'shield-lock-fill',
                    activePattern: ':Admin:UserRole:*',
                ),
                new MenuItem(
                    label: 'Session uživatelů',
                    destination: ':Admin:UserSession:default',
                    icon: 'person-badge-fill',
                    activePattern: ':Admin:UserSession:default',
                ),
                new MenuItem(
                    label: 'API uživatelé',
                    destination: ':Admin:ApiUser:default',
                    icon: 'key-fill',
                    activePattern: ':Admin:ApiUser:*',
                ),
                new MenuItem(
                    label: 'E-maily',
                    destination: null,
                    icon: 'envelope-fill',
                    activePattern: ':Admin:Email:*',
                    children: [
                        new MenuItem(
                            label: 'Odeslané e-maily',
                            destination: ':Admin:Email:list',
                            icon: 'send-fill',
                            activePattern: ':Admin:Email:list',
                        ),
                        new MenuItem(
                            label: 'Nový e-mail',
                            destination: ':Admin:Email:compose',
                            icon: 'pencil-square',
                            activePattern: ':Admin:Email:compose',
                        ),
                        new MenuItem(
                            label: 'Fronta e-mailů',
                            destination: ':Admin:Email:queue',
                            icon: 'hourglass-split',
                            activePattern: ':Admin:Email:queue',
                        ),
                    ],
                ),
                new MenuItem(
                    label: 'Plánované úlohy',
                    destination: null,
                    icon: 'clock-fill',
                    activePattern: ':Admin:ScheduledJob:*',
                    children: [
                        new MenuItem(
                            label: 'Definice úloh',
                            destination: ':Admin:ScheduledJob:default',
                            icon: 'list-task',
                            activePattern: ':Admin:ScheduledJob:default',
                        ),
                        new MenuItem(
                            label: 'Historie běhů',
                            destination: ':Admin:ScheduledJob:runs',
                            icon: 'journal-text',
                            activePattern: ':Admin:ScheduledJob:runs',
                        ),
                    ],
                ),
                new MenuItem(
                    label: 'Nastavení',
                    destination: ':Admin:AppSettings:general',
                    icon: 'gear-fill',
                    activePattern: ':Admin:AppSettings:*',
                ),
            ]),
        ];
    }
}
