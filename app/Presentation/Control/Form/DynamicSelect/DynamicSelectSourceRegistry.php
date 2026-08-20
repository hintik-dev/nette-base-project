<?php declare(strict_types=1);
namespace App\Presentation\Control\Form\DynamicSelect;

/**
 * Sdružuje všechny DynamicSelectSource. Registruje se ručně v config/services.neon,
 * kde se jí předá seznam přes typed() — stejný vzor jako ScopeResolverRegistry.
 * Nový zdroj tak stačí jen naimplementovat, žádná další registrace není potřeba.
 */
final class DynamicSelectSourceRegistry
{
    /** @var array<string, DynamicSelectSource>|null */
    private ?array $byKey = null;


    /** @param list<DynamicSelectSource> $sources */
    public function __construct(
        private readonly array $sources,
    ) {
    }


    public function find(string $key): ?DynamicSelectSource
    {
        return $this->indexed()[$key] ?? null;
    }


    /** @return array<string, DynamicSelectSource> */
    private function indexed(): array
    {
        if ($this->byKey !== null) {
            return $this->byKey;
        }

        $byKey = [];
        foreach ($this->sources as $source) {
            $byKey[$source->getKey()] = $source;
        }

        return $this->byKey = $byKey;
    }
}
