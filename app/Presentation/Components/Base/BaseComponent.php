<?php declare(strict_types=1);

namespace App\Presentation\Components\Base;

use Nette\Application\UI\Control;

class BaseComponent extends Control
{
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

        $this->getTemplate()->setFile($this->latteFile . '.latte');
        $this->getTemplate()->componentName = $this->getComponentName();
        $this->getTemplate()->render();
    }
}
