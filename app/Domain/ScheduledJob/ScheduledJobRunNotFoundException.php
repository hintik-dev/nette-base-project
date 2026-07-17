<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

use App\Model\Exception\EntityNotFoundException;

class ScheduledJobRunNotFoundException extends EntityNotFoundException
{
    public function __construct(int $id)
    {
        parent::__construct('ScheduledJobRun', ['id' => $id]);
    }
}
