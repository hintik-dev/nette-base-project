<?php

namespace App\Presentation\Components\Base;

use Nette\Application\UI\Control;

class BaseComponent extends Control
{
    protected ?string $latteFile = null;
    private ?string $componentName = null {
        get {
            if ($this->componentName === null)
            {
                $this->componentName = self::getReflection()->getShortName();
            }

            return $this->componentName;
        }
    }
    private ?string $componentNameWithPath = null {
        get {
            if ($this->componentNameWithPath === null)
            {
                $fileName = $this->getReflection()->getFileName();
                if (!empty($fileName))
                {
                    $this->componentNameWithPath = str_replace(".php", "", $fileName);
                }
            }

            return $this->componentNameWithPath;
        }
    }

    public function render(mixed $params = null): void
    {
        if (empty($this->latteFile))
        {
            $this->latteFile = $this->componentNameWithPath;
        }

        $this->getTemplate()->setFile($this->latteFile . '.latte');
        $this->getTemplate()->componentName = $this->componentName;
        $this->getTemplate()->render();
    }

}
