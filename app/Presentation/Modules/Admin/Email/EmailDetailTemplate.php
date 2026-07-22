<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\Email;

use App\Domain\Email\SentEmail;
use App\Model\Latte\BaseTemplate;

final class EmailDetailTemplate extends BaseTemplate
{
    public SentEmail $email;
}
