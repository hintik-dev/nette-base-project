<?php declare(strict_types=1);
namespace App\Presentation\Modules\Base;

use App\Model\Utils\FlashMessage;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\TFlashMessage;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;

class BasePresenter extends Presenter
{
    use TFlashMessage;

    public function addComponent(IComponent $component, ?string $name, ?string $insertBefore = null): static
    {
        if ($component instanceof BaseComponent) {
            $component->onFlash[] = function (FlashMessage $flashMessage): void {
                $this->flash($flashMessage);
            };
        }

        return parent::addComponent($component, $name, $insertBefore);
    }
}
