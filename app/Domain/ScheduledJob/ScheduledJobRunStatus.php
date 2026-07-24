<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

enum ScheduledJobRunStatus: string
{
    case Scheduled = 'scheduled';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';

    public function toLabel(): string
    {
        return match($this) {
            self::Scheduled => 'Naplánováno',
            self::Running   => 'Běží',
            self::Completed => 'Dokončeno',
            self::Failed    => 'Chyba',
        };
    }

    public function toBadgeClass(): string
    {
        return match($this) {
            self::Scheduled => 'badge bg-info text-dark',
            self::Running   => 'badge bg-warning text-dark',
            self::Completed => 'badge bg-success',
            self::Failed    => 'badge bg-danger',
        };
    }
}
