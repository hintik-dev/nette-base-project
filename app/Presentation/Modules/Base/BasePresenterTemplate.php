<?php declare(strict_types=1);
namespace App\Presentation\Modules\Base;

use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Security\User;
use Nette\Utils\Arrays;
use Override;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;
use RuntimeException;
use Throwable;

abstract class BasePresenterTemplate extends Template
{
    /** @var Presenter $presenter */
    public Presenter $presenter;

    /** @var Control $control */
    public Control $control;

    /** @var User */
    public User $user;

    /** @var string */
    public string $baseUrl;

    /** @var string */
    public string $basePath;


    /**
     * Vykreslení šablony.
     * @param string|null $file
     * @param array<string, mixed> $params
     */
    #[Override]
    final public function render(?string $file = null, array $params = []): void
    {
        Arrays::toObject($params, $this);
        $this->checkIfPropertiesIsFilled();

        parent::render($file, $params);
    }


    /**
     * Vykreslení šablony do řetězce.
     * @param string|null $file
     * @param array<string, mixed> $params
     * @return string
     * @throws ReflectionException
     */
    #[Override]
    final public function renderToString(?string $file = null, array $params = []): string
    {
        Arrays::toObject($params, $this);
        $this->checkIfPropertiesIsFilled();

        return parent::renderToString($file, $params);
    }


    /**
     * Zkontroluje, zda jsou všechny proměnné třídy inicializované,
     * pokud ne vyhazuje výjímku.
     * @throws RuntimeException
     */
    final protected function checkIfPropertiesIsFilled(): void
    {
        $uninitializedProps = [];

        foreach (new ReflectionObject($this)->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            if (!$prop->isInitialized($this)) {
                $uninitializedProps[] = $prop->getName();
            }
        }

        if ($uninitializedProps) {
            throw new RuntimeException(
                sprintf('Template has uninitialized public variables: %s.', implode(', ', $uninitializedProps)),
            );
        }
    }


    /**
     * Vykreslení instance šablony jako řetězec.
     * @return string
     * @throws Throwable
     */
    #[Override]
    final public function __toString(): string
    {
        $this->checkIfPropertiesIsFilled();

        return parent::__toString();
    }
}
