<?php declare(strict_types=1);
namespace App\Domain\ValueStorage;

readonly class ValueStorage
{
    public function __construct(
        public int $id,
        public string $category,
        public string $key,
        public ?string $value,
    ) {
    }
}
