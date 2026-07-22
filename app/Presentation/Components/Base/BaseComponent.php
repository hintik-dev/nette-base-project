<?php declare(strict_types=1);

namespace App\Presentation\Components\Base;

use App\Model\Latte\BaseTemplate;
use App\Presentation\Control\TComponentFlashMessage;
use Nette\Application\UI\Control;

/**
 * @property-read BaseTemplate $template
 */
class BaseComponent extends Control
{
    use TComponentFlashMessage;

    protected ?string $latteFile = null;

    private ?string $componentName = null;

    private ?string $componentNameWithPath = null;


    private function getComponentName(): string
    {
        if ($this->componentName === null)
        {
            $this->componentName = self::getReflection()->getShortName();
        }

        return $this->componentName;
    }


    private function getComponentNameWithPath(): ?string
    {
        if ($this->componentNameWithPath === null)
        {
            $fileName = $this->getReflection()->getFileName();
            if (!empty($fileName))
            {
                $this->componentNameWithPath = str_replace('.php', '', $fileName);
            }
        }

        return $this->componentNameWithPath;
    }


    public function render(mixed $params = null): void
    {
        if (empty($this->latteFile))
        {
            $this->latteFile = $this->getComponentNameWithPath();
        }

        $this->template->setFile($this->latteFile . '.latte');
        $this->template->componentName = $this->getComponentName();
        $this->template->render();
    }
}
