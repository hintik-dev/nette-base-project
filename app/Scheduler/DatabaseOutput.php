<?php declare(strict_types=1);

namespace App\Scheduler;

use Symfony\Component\Console\Output\Output;

/**
 * Zachytí každý výpis z run() s přesným časem.
 * Po dokončení běhu se záznamy uloží do tabulky scheduled_job_run_output.
 */
final class DatabaseOutput extends Output
{
    /** @var array{message: string, createdAt: \DateTimeImmutable}[] */
    private array $entries = [];


    protected function doWrite(string $message, bool $newline): void
    {
        // Odstraní ANSI escape kódy (barvy, formátování)
        $clean = (string) preg_replace('/\x1B\[[0-9;]*[mGKHF]/u', '', $message);

        if ($clean !== '') {
            $this->entries[] = [
                'message'   => $clean,
                'createdAt' => new \DateTimeImmutable(),
            ];
        }
    }


    /**
     * @return array{message: string, createdAt: \DateTimeImmutable}[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }
}
