<?php declare(strict_types=1);
namespace App\Model\Exception;

use RuntimeException;
use Throwable;

class EntityNotFoundException extends RuntimeException
{
    /**
     * @param string $entityName
     * @param array<string, mixed> $criteria
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $entityName, array $criteria = [], int $code = 0, ?Throwable $previous = null)
    {
        $criteriaMessage = $this->formatCriteria($criteria);
        $message = sprintf('%s not found. %s', $entityName, $criteriaMessage);

        parent::__construct($message, $code, $previous);
    }


    /**
     * @param array<string, mixed> $criteria
     * @return string
     */
    private function formatCriteria(array $criteria): string
    {
        if (empty($criteria)) {
            return '';
        }

        $formatted = [];
        foreach ($criteria as $key => $value) {
            $formatted[] = sprintf('%s: %s', $key, $value);
        }

        return 'Criteria - ' . implode(', ', $formatted);
    }
}
