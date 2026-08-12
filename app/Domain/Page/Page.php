<?php declare(strict_types=1);
namespace App\Domain\Page;

use DateTime;
use Nette\Security\Resource;

readonly class Page implements Resource
{
    public const string RESOURCE_ID = 'page';

    /**
     * @param array<string, mixed> $content Puck data payload: {content: [{type, props}], root: {props}}
     */
    public function __construct(
        public int $id,
        public string $slug,
        public string $title,
        public ?int $authorId,
        public array $content,
        public PageStatus $status,
        public ?DateTime $publishedAt,
    ) {
    }


    /** Stránka bez autora nepatří nikomu — vlastnické oprávnění na ni nezabere. */
    public function isAuthoredBy(int $userId): bool
    {
        return $this->authorId === $userId;
    }


    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
