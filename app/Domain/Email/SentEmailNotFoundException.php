<?php declare(strict_types=1);

namespace App\Domain\Email;

use App\Model\Exception\EntityNotFoundException;

class SentEmailNotFoundException extends EntityNotFoundException
{
    public function __construct(int $id)
    {
        parent::__construct('SentEmail', ['id' => $id]);
    }
}
