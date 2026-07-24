<?php declare(strict_types=1);

namespace App\Domain\Email;

enum SentEmailStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
}
