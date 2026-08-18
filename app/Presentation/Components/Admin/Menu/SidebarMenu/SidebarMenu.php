<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Menu\SidebarMenu;

use App\Domain\UserSettings\AppearanceTheme;
use App\Presentation\Components\Admin\Menu\AdminMenuFactory;
use App\Presentation\Components\Admin\Menu\MenuItem;
use App\Presentation\Components\Admin\Menu\MenuPermissionResolver;
use App\Presentation\Components\Admin\Menu\MenuSection;
use App\Presentation\Components\Base\BaseComponent;

/**
 * Vykresluje boční menu podle definice v AdminMenuFactory.
 *
 * Filtrování podle oprávnění probíhá tady, ne v šabloně — ta dostane už jen
 * to, co má uživatel vidět. Prázdná sekce ani prázdná rozbalovací skupina
 * se nevykreslí, takže se hlavičky nemusí hlídat ručně.
 *
 * @property-read SidebarMenuTemplate $template
 */
class SidebarMenu extends BaseComponent
{
    public function __construct(
        private readonly AdminMenuFactory $adminMenuFactory,
        private readonly MenuPermissionResolver $permissionResolver,
        private readonly AppearanceTheme $adminTheme,
    ) {
    }


    public function render(mixed $params = null): void
    {
        $this->template->sections = $this->getVisibleSections();
        $this->template->adminTheme = $this->adminTheme;

        parent::render($params);
    }


    /** @return list<MenuSection> */
    private function getVisibleSections(): array
    {
        $sections = [];

        foreach ($this->adminMenuFactory->create() as $section) {
            $items = $this->filterItems($section->items);

            if ($items !== []) {
                $sections[] = new MenuSection($section->label, $items);
            }
        }

        return $sections;
    }


    /**
     * @param list<MenuItem> $items
     * @return list<MenuItem>
     */
    private function filterItems(array $items): array
    {
        $visible = [];

        foreach ($items as $item) {
            if ($item->isGroup()) {
                $children = $this->filterItems($item->children);

                // Skupina bez viditelného potomku nemá co nabídnout.
                if ($children !== []) {
                    $visible[] = new MenuItem(
                        label: $item->label,
                        destination: null,
                        icon: $item->icon,
                        activePattern: $item->activePattern,
                        children: $children,
                    );
                }

                continue;
            }

            if ($this->permissionResolver->isAllowed((string) $item->destination)) {
                $visible[] = $item;
            }
        }

        return $visible;
    }
}
