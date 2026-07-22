<?php declare(strict_types=1);
namespace App\Model\Latte;

use App\Model\Security\SecurityUser;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Utils\Arrays;
use Override;
use ReflectionObject;
use ReflectionProperty;
use RuntimeException;
use Throwable;

/**
 * Společný základ pro typované šablony presenterů i komponent.
 * Konkrétní presentery/komponenty, kterým se do šablony předávají
 * specifická data, dědí a přidávají vlastní typované vlastnosti.
 */
class BaseTemplate extends Template
{
    public Presenter $presenter;

    public Control $control;

    public SecurityUser $_user;

    public string $baseUrl;

    public string $basePath;

    /** @var array<array{message: string, type: string}> */
    public array $flashes = [];

    public ?string $componentName = null;

    public self $_template;

    public FilterExecutor $_filters;


    /**
     * Vykreslení šablony.
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
     * @param array<string, mixed> $params
     */
    #[Override]
    final public function renderToString(?string $file = null, array $params = []): string
    {
        Arrays::toObject($params, $this);
        $this->checkIfPropertiesIsFilled();

        return parent::renderToString($file, $params);
    }


    /**
     * Zkontroluje, zda jsou všechny public vlastnosti šablony inicializované,
     * pokud ne vyhazuje výjimku.
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
     * @throws Throwable
     */
    #[Override]
    final public function __toString(): string
    {
        $this->checkIfPropertiesIsFilled();

        return parent::__toString();
    }
}
