<?php declare(strict_types=1);
namespace App\Domain\Page;

use Nette\Bridges\ApplicationLatte\LatteFactory;
use RuntimeException;

/**
 * PHP protějšek k React Puck configu na admin straně — každý typ bloku
 * z editoru musí mít odpovídající Latte šablonu v Web/Page/blocks/{type}.latte.
 */
final class BlockRenderer
{
    private const string BLOCKS_DIR = __DIR__ . '/../../Presentation/Modules/Web/Page/blocks';

    public function __construct(
        private readonly LatteFactory $latteFactory,
    ) {
    }


    /**
     * @param array<int, array{type?: string, props?: array<string, mixed>}> $blocks
     */
    public function render(array $blocks): string
    {
        $latte = $this->latteFactory->create();

        return implode('', array_map(
            fn(array $block): string => $this->renderOne($latte, $block),
            $blocks,
        ));
    }


    /**
     * @param array{type?: string, props?: array<string, mixed>} $block
     */
    private function renderOne(\Latte\Engine $latte, array $block): string
    {
        $type = $block['type'] ?? null;

        if ($type === null || $type === '') {
            return '';
        }

        $file = self::BLOCKS_DIR . '/' . $type . '.latte';

        if (!is_file($file)) {
            throw new RuntimeException(sprintf('Neznámý typ bloku "%s" — chybí šablona %s.', $type, $file));
        }

        return $latte->renderToString($file, [
            'props' => $block['props'] ?? [],
            'blockRenderer' => $this,
        ]);
    }
}
