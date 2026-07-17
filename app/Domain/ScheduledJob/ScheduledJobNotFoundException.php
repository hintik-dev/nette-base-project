<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

use App\Model\Exception\EntityNotFoundException;

class ScheduledJobNotFoundException extends EntityNotFoundException
{
    public function __construct(int $id)
    {
        parent::__construct('ScheduledJob', ['id' => $id]);
    }
}
