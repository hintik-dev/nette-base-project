<?php declare(strict_types=1);

namespace App\Domain\Email;

enum MailQueueStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
}
